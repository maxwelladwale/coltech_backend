<?php

namespace App\Services;

use App\Models\Order;
use LaravelDaily\Invoices\Invoice;
use LaravelDaily\Invoices\Classes\Buyer;
use LaravelDaily\Invoices\Classes\InvoiceItem;
use Illuminate\Support\Facades\Storage;

class InvoiceService
{
    /**
     * Generate PDF invoice for an order
     *
     * @param Order $order
     * @return array ['url' => string, 'qr_code' => string, 'path' => string]
     */
    public function generateInvoice(Order $order): array
    {
        // Load order relationships
        $order->load('items', 'user');

        // Create customer/buyer
        $customer = new Buyer([
            'name' => $order->getCustomerName(),
            'custom_fields' => [
                'email' => $order->getCustomerEmail(),
                'phone' => $order->shipping_phone,
                'address' => $order->shipping_address . ', ' . $order->shipping_city . ', ' . $order->shipping_county,
            ],
        ]);

        // Create invoice items
        $items = [];
        foreach ($order->items as $item) {
            $items[] = InvoiceItem::make($item->product_name)
                ->pricePerUnit($item->unit_price)
                ->quantity($item->quantity)
                ->description($item->product_category ? "Category: {$item->product_category}" : '')
                ->discount(0);
        }

        // Add shipping/installation as a line item if applicable
        if ($order->shipping > 0) {
            $items[] = InvoiceItem::make('Installation Service')
                ->pricePerUnit($order->shipping)
                ->quantity(1)
                ->description($order->installation_method === 'technician' ? 'Professional installation' : 'Shipping');
        }

        // Generate the invoice
        $invoice = Invoice::make()
            ->series('INV')
            ->sequence($order->id)
            ->serialNumberFormat('{SERIES}-{SEQUENCE}')
            ->buyer($customer)
            ->date($order->created_at)
            ->dateFormat('d/m/Y')
            ->payUntilDays(config('invoices.date.pay_until_days', 7))
            ->currencySymbol('KES')
            ->currencyCode('KES')
            ->currencyFormat('{SYMBOL} {VALUE}')
            ->currencyThousandsSeparator(',')
            ->currencyDecimalPoint('.')
            ->filename($order->order_number . '-invoice')
            ->addItems($items)
            ->notes('Thank you for your business!')
            ->logo(public_path('images/logo.png'))
            ->save('public/invoices');

        // Get the saved file path
        $filename = $order->order_number . '-invoice.pdf';
        $path = 'invoices/' . $filename;

        // Generate public URL
        $url = Storage::url($path);

        // Generate QR code data (could be a URL to verify the invoice)
        $qrData = url('/invoices/' . $order->order_number);

        return [
            'url' => $url,
            'qr_code' => $qrData,
            'path' => $path,
            'filename' => $filename,
        ];
    }

    /**
     * Regenerate invoice for an order (if it was deleted or needs updating)
     *
     * @param Order $order
     * @return array
     */
    public function regenerateInvoice(Order $order): array
    {
        // Delete old invoice if exists
        if ($order->invoice_url) {
            $oldPath = str_replace('/storage/', '', parse_url($order->invoice_url, PHP_URL_PATH));
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        return $this->generateInvoice($order);
    }

    /**
     * Download invoice PDF
     *
     * @param Order $order
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadInvoice(Order $order)
    {
        $path = str_replace('/storage/', '', parse_url($order->invoice_url, PHP_URL_PATH));

        if (!Storage::disk('public')->exists($path)) {
            // Regenerate if missing
            $this->regenerateInvoice($order);
        }

        return Storage::disk('public')->download($path, $order->order_number . '-invoice.pdf');
    }
}
