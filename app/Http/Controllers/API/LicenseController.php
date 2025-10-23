<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\License;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class LicenseController extends Controller
{
    /**
     * Get license by vehicle registration
     * Matches: ILicenseService.getLicenseByVehicle()
     * 
     * GET /api/licenses/vehicle/{registration}
     */
    public function getByVehicle(string $registration): JsonResponse
    {
        $license = License::with(['order', 'user'])
            ->vehicle($registration)
            ->first();

        if (!$license) {
            return response()->json([
                'message' => 'License not found for this vehicle'
            ], 404);
        }

        return response()->json($license);
    }

    /**
     * Check license status
     * Matches: ILicenseService.checkLicenseStatus()
     * 
     * GET /api/licenses/check/{registration}
     */
    public function checkStatus(string $registration): JsonResponse
    {
        $license = License::vehicle($registration)->first();

        if (!$license) {
            return response()->json([
                'isActive' => false,
                'message' => 'No license found for this vehicle'
            ]);
        }

        return response()->json([
            'isActive' => $license->isActive(),
            'expiryDate' => $license->expiry_date,
            'daysRemaining' => $license->daysRemaining()
        ]);
    }

    /**
     * Activate new license
     * Matches: ILicenseService.activateLicense()
     * 
     * POST /api/licenses/activate
     */
    public function activate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'orderId' => 'required|exists:orders,id',
            'mdvrSerialNumber' => 'required|string',
            'vehicleRegistration' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if license already exists for this vehicle
        $existing = License::vehicle($request->vehicleRegistration)->first();
        if ($existing) {
            return response()->json([
                'message' => 'License already exists for this vehicle'
            ], 409);
        }

        // Get order to determine license type
        $order = \App\Models\Order::with('items.product')->find($request->orderId);
        
        // Find MDVR product in order to get license type
        $mdvrProduct = $order->items->first(function ($item) {
            return $item->product->category === 'mdvr';
        });

        if (!$mdvrProduct) {
            return response()->json([
                'message' => 'No MDVR found in order'
            ], 400);
        }

        $licenseType = $mdvrProduct->product->license_type ?? 'non-ai';
        $renewalPrice = $licenseType === 'ai' ? 12000 : 8000;

        // Create license
        $license = License::create([
            'license_key' => License::generateLicenseKey(),
            'mdvr_serial_number' => $request->mdvrSerialNumber,
            'vehicle_registration' => $request->vehicleRegistration,
            'type' => $licenseType,
            'status' => 'active',
            'activation_date' => now(),
            'expiry_date' => now()->addMonths(12),
            'renewal_price' => $renewalPrice,
            'order_id' => $request->orderId,
            'user_id' => $order->user_id
        ]);

        // TODO: Send license activation email/SMS

        return response()->json($license, 201);
    }

    /**
     * Renew license
     * Matches: ILicenseService.renewLicense()
     * 
     * POST /api/licenses/{id}/renew
     */
    public function renew(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'duration' => 'integer|min:1|max:36'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $license = License::find($id);

        if (!$license) {
            return response()->json([
                'message' => 'License not found'
            ], 404);
        }

        $duration = $request->input('duration', 12);
        $license->renew($duration);

        // TODO: Send renewal confirmation email/SMS

        return response()->json($license);
    }

    /**
     * Get renewal price
     * Matches: ILicenseService.getRenewalPrice()
     * 
     * GET /api/licenses/renewal-price?type=ai
     */
    public function getRenewalPrice(Request $request): JsonResponse
    {
        $type = $request->input('type', 'non-ai');
        $price = $type === 'ai' ? 12000 : 8000;

        return response()->json([
            'type' => $type,
            'price' => $price
        ]);
    }
}