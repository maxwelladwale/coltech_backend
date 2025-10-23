<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\OtpVerification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class CertificateController extends Controller
{
    /**
     * Send OTP for verification
     * Matches: ICertificateService.sendOTP()
     * 
     * POST /api/certificates/send-otp
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'email' => 'nullable|email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Generate OTP
        $otpCode = OtpVerification::generateOTP();
        $expiresAt = now()->addMinutes(5);

        // Save OTP
        $otp = OtpVerification::create([
            'phone' => $request->phone,
            'email' => $request->email,
            'otp_code' => $otpCode,
            'expires_at' => $expiresAt,
            'purpose' => 'certificate_verification'
        ]);

        // TODO: Send OTP via SMS (Africa's Talking)
        // SMS::send($request->phone, "Your COLTECH verification code is: {$otpCode}");

        // For development, return OTP (REMOVE IN PRODUCTION!)
        return response()->json([
            'sent' => true,
            'expiresIn' => 300,
            'otp' => config('app.debug') ? $otpCode : null // Only in debug mode
        ]);
    }

    /**
     * Verify OTP
     * Matches: ICertificateService.verifyOTP()
     * 
     * POST /api/certificates/verify-otp
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'otp' => 'required|string|size:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $otp = OtpVerification::where('phone', $request->phone)
            ->where('otp_code', $request->otp)
            ->unverified()
            ->notExpired()
            ->first();

        if (!$otp) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid or expired OTP'
            ], 400);
        }

        $otp->verify();

        return response()->json([
            'valid' => true,
            'message' => 'OTP verified successfully'
        ]);
    }

    /**
     * Verify QR code with OTP
     * Matches: ICertificateService.verifyQRCode()
     * 
     * POST /api/certificates/verify-qr
     */
    public function verifyQr(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'qrCode' => 'required|string',
            'personalDetails' => 'required|array',
            'personalDetails.fullName' => 'required|string',
            'personalDetails.phone' => 'required|string',
            'otp' => 'required|string|size:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // First verify OTP
        $otp = OtpVerification::where('phone', $request->personalDetails['phone'])
            ->where('otp_code', $request->otp)
            ->where('verified', true)
            ->where('verified_at', '>=', now()->subMinutes(10)) // OTP valid for 10 min after verification
            ->first();

        if (!$otp) {
            return response()->json([
                'isValid' => false,
                'verified' => false,
                'message' => 'OTP not verified or expired'
            ], 400);
        }

        // Find certificate
        $certificate = Certificate::with(['order', 'license'])
            ->where('qr_code', $request->qrCode)
            ->first();

        if (!$certificate) {
            return response()->json([
                'isValid' => false,
                'verified' => false,
                'message' => 'Certificate not found'
            ]);
        }

        // Check if certificate is valid
        if (!$certificate->isValid()) {
            return response()->json([
                'isValid' => false,
                'verified' => true,
                'message' => 'Certificate has expired',
                'certificate' => $certificate
            ]);
        }

        return response()->json([
            'isValid' => true,
            'verified' => true,
            'message' => 'Certificate verified successfully',
            'certificate' => $certificate
        ]);
    }

    /**
     * Generate certificate
     * Matches: ICertificateService.generateCertificate()
     * 
     * POST /api/certificates/generate
     */
    public function generate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:installation,license,product',
            'orderId' => 'required|exists:orders,id',
            'details' => 'required|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $certificate = Certificate::create([
            'certificate_number' => Certificate::generateCertificateNumber(),
            'type' => $request->type,
            'qr_code' => Certificate::generateQRCode(),
            'issued_to' => $request->details['issuedTo'] ?? 'Customer',
            'issued_date' => now(),
            'expiry_date' => $request->details['expiryDate'] ?? null,
            'details' => $request->details,
            'order_id' => $request->orderId,
            'license_id' => $request->details['licenseId'] ?? null
        ]);

        // TODO: Generate PDF certificate
        // TODO: Send certificate via email

        return response()->json($certificate, 201);
    }
}