<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use App\Notifications\OrderAssignedToGarageNotification;
use App\Notifications\OrderPlacedNotification;
use App\Notifications\OrderStatusChangedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Create new order
     * Matches: IOrderService.createOrder()
     *
     * POST /api/orders
     */
    public function store(Request $request): JsonResponse
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'shippingAddress' => 'required|array',
            'shippingAddress.fullName' => 'required|string',
            'shippingAddress.phone' => 'required|string',
            'shippingAddress.email' => 'required|email',
            'shippingAddress.address' => 'required|string',
            'shippingAddress.city' => 'required|string',
            'shippingAddress.county' => 'required|string',
            'paymentMethod' => 'required|in:mpesa,card,bank',
            'cartItems' => 'required|array|min:1',
            'cartItems.*.productId' => 'required|exists:products,id',
            'cartItems.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Calculate totals
            $subtotal = 0;
            $cartItems = [];

            foreach ($request->cartItems as $item) {
                $product = Product::find($item['productId']);

                if (! $product || ! $product->in_stock) {
                    throw new \Exception("Product {$product->name} is out of stock");
                }

                $itemTotal = $product->price * $item['quantity'];
                $subtotal += $itemTotal;

                $cartItems[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'total' => $itemTotal,
                ];
            }

            // Calculate installation cost
            $installationCost = 0;
            if ($request->has('installationDetails') &&
                $request->installationDetails['method'] === 'technician') {
                $installationCost = 5000;
            }

            $total = $subtotal + $installationCost;

            // Create order (use sanctum guard explicitly for API)
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => auth('sanctum')->id() ?? null,
                'guest_email' => $request->shippingAddress['email'],
                'subtotal' => $subtotal,
                'tax' => 0,
                'shipping' => $installationCost,
                'total' => $total,
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => $request->paymentMethod,
                'shipping_name' => $request->shippingAddress['fullName'],
                'shipping_phone' => $request->shippingAddress['phone'],
                'shipping_email' => $request->shippingAddress['email'],
                'shipping_address' => $request->shippingAddress['address'],
                'shipping_city' => $request->shippingAddress['city'],
                'shipping_county' => $request->shippingAddress['county'],
                'shipping_postal_code' => $request->shippingAddress['postalCode'] ?? null,
                'installation_method' => $request->installationDetails['method'] ?? null,
                'garage_id' => $request->installationDetails['garageId'] ?? null,
                'vehicle_registration' => $request->installationDetails['vehicleRegistration'] ?? null,
                'vehicle_make' => $request->installationDetails['vehicleMake'] ?? null,
                'vehicle_model' => $request->installationDetails['vehicleModel'] ?? null,
            ]);

            // Create order items
            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'product_sku' => $item['product']->sku,
                    'product_category' => $item['product']->category,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['product']->price,
                    'total_price' => $item['total'],
                ]);

                // Decrease stock
                $item['product']->decreaseStock($item['quantity']);
            }

            DB::commit();

            // Load order with items
            $order->load('items', 'garage', 'user');

            // Generate invoice for the order
            try {
                $order->generateInvoice();
                $order->refresh(); // Refresh to get the updated invoice_url
                \Log::info('Invoice generated successfully (API)', [
                    'order_number' => $order->order_number,
                    'invoice_url' => $order->invoice_url,
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to generate invoice (API)', [
                    'order_number' => $order->order_number,
                    'error' => $e->getMessage(),
                ]);
            }

            // Send order confirmation email to customer
            if ($order->user) {
                // Registered user - send via user model
                \Log::info('Queueing order confirmation email (registered user)', [
                    'order_number' => $order->order_number,
                    'customer_id' => $order->user->id,
                    'customer_email' => $order->user->email,
                    'invoice_url' => $order->invoice_url,
                ]);
                $order->user->notify(new OrderPlacedNotification($order));
            } else {
                // Guest user - send via email notification
                \Log::info('Queueing order confirmation email (guest)', [
                    'order_number' => $order->order_number,
                    'guest_email' => $order->shipping_email,
                    'invoice_url' => $order->invoice_url,
                ]);
                \Illuminate\Support\Facades\Notification::route('mail', $order->shipping_email)
                    ->notify(new OrderPlacedNotification($order));
            }

            // Send notification to admin users
            $adminUsers = User::where('role', 'admin')->get();
            \Log::info('Queueing admin notifications', [
                'order_number' => $order->order_number,
                'admin_count' => $adminUsers->count(),
                'admin_emails' => $adminUsers->pluck('email')->toArray(),
                'invoice_url' => $order->invoice_url,
            ]);
            foreach ($adminUsers as $admin) {
                $admin->notify(new NewOrderNotification($order));
            }

            // Send notification to garage if installation is required
            if ($order->garage_id && $order->garage) {
                // Create a simple notifiable object for the garage
                $garageNotifiable = new class($order->garage->email, $order->garage->name) {
                    public $email;
                    public $name;

                    public function __construct($email, $name) {
                        $this->email = $email;
                        $this->name = $name;
                    }

                    public function routeNotificationForMail() {
                        return $this->email;
                    }
                };

                \Illuminate\Support\Facades\Notification::route('mail', $order->garage->email)
                    ->notify(new OrderAssignedToGarageNotification($order));
            }

            return response()->json($order, 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get order by ID
     * Matches: IOrderService.getOrderById()
     *
     * GET /api/orders/{id}
     */
    public function show(string $id): JsonResponse
    {
        $order = Order::with(['items.product', 'garage', 'user'])
            ->find($id);

        if (! $order) {
            return response()->json([
                'message' => 'Order not found',
            ], 404);
        }

        return response()->json($order);
    }

    /**
     * Get orders by user
     * Matches: IOrderService.getOrdersByUser()
     *
     * GET /api/orders?userId={userId}
     */
    public function index(Request $request): JsonResponse
    {
        $query = Order::with('items.product');

        if ($request->has('userId')) {
            $query->where('user_id', $request->userId);
        }

        if ($request->has('guestEmail')) {
            $query->where('guest_email', $request->guestEmail);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        return response()->json($orders);
    }

    /**
     * Find order by order number and email (for guest tracking)
     * NEW: For guest order tracking
     *
     * POST /api/orders/track
     */
    public function track(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'orderNumber' => 'required|string',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $order = Order::with(['items.product', 'garage'])
            ->where('order_number', $request->orderNumber)
            ->where(function ($q) use ($request) {
                $q->where('shipping_email', $request->email)
                    ->orWhereHas('user', function ($query) use ($request) {
                        $query->where('email', $request->email);
                    });
            })
            ->first();

        if (! $order) {
            return response()->json([
                'message' => 'Order not found',
            ], 404);
        }

        return response()->json($order);
    }

    /**
     * Update order status
     * Matches: IOrderService.updateOrderStatus()
     *
     * PATCH /api/orders/{id}/status
     */
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $order = Order::with('user')->find($id);

        if (! $order) {
            return response()->json([
                'message' => 'Order not found',
            ], 404);
        }

        $oldStatus = $order->status;
        $order->update(['status' => $request->status]);

        // Send notification to customer
        if ($order->user) {
            $order->user->notify(new OrderStatusChangedNotification($order, $oldStatus, $request->status));
        }

        return response()->json($order);
    }

    /**
     * Download invoice for an order
     * NEW: Download invoice PDF
     *
     * GET /api/orders/{id}/invoice
     */
    public function downloadInvoice(string $id)
    {
        $order = Order::find($id);

        if (! $order) {
            return response()->json([
                'message' => 'Order not found',
            ], 404);
        }

        if (! $order->hasInvoice()) {
            return response()->json([
                'message' => 'Invoice not available for this order',
            ], 404);
        }

        try {
            $invoiceService = app(\App\Services\InvoiceService::class);
            return $invoiceService->downloadInvoice($order);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to download invoice',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Regenerate invoice for an order
     * NEW: Force regenerate invoice PDF
     *
     * POST /api/orders/{id}/invoice/regenerate
     */
    public function regenerateInvoice(string $id): JsonResponse
    {
        $order = Order::with('items')->find($id);

        if (! $order) {
            return response()->json([
                'message' => 'Order not found',
            ], 404);
        }

        try {
            $order->generateInvoice();
            $order->refresh();

            return response()->json([
                'message' => 'Invoice regenerated successfully',
                'invoice_url' => $order->invoice_url,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to regenerate invoice',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
