<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function signup(Request $request)
    {
        // Validate incoming request
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'sometimes|in:customer,admin,sales,support'
        ]);

        // Create user (default role is customer if not specified)
        $user = User::create([
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => $validated['password'], // Auto-hashed by model cast
            'role' => $validated['role'] ?? 'customer',
        ]);

        // Send welcome email
        \Log::info('Sending welcome email after registration', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
        $user->notify(new WelcomeNotification($user));

        // Send email verification notification
        \Log::info('Sending email verification after registration', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
        $user->sendEmailVerificationNotification();

        // Create authentication token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Return user data and token
        return response()->json([
            'message' => 'User registered successfully. Please check your email to verify your account.',
            'user' => [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
            ],
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    /**
     * Login user and return token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    public function login(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Find user by email
        $user = User::where('email', $request->email)->first();

        // Check if user exists and password is correct
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Revoke all existing tokens for this user (optional - single device login)
        // $user->tokens()->delete();

        // Create new authentication token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Return user data and token
        return response()->json([
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'created_at' => $user->created_at,
            ],
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }

    /**
     * Logout user (revoke current token)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Revoke the current user's token (the one used for authentication)
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ], 200);
    }

    /**
     * Get authenticated user profile
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile(Request $request)
    {
        return response()->json([
            'user' => [
                'id' => $request->user()->id,
                'full_name' => $request->user()->full_name,
                'email' => $request->user()->email,
                'phone' => $request->user()->phone,
                'role' => $request->user()->role,
                'email_verified_at' => $request->user()->email_verified_at,
                'created_at' => $request->user()->created_at,
                'updated_at' => $request->user()->updated_at,
            ]
        ], 200);
    }

    /**
     * Verify user email
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function verify(Request $request)
    {
        $user = User::findOrFail($request->route('id'));

        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');

        // Check if the hash matches
        if (!hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            \Log::warning('Invalid email verification attempt', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            // Redirect to frontend with error
            return redirect()->to($frontendUrl . '/auth/verification/failed?reason=invalid');
        }

        // Check if email is already verified
        if ($user->hasVerifiedEmail()) {
            \Log::info('Email verification attempted for already verified user', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            // Redirect to frontend with info message
            return redirect()->to($frontendUrl . '/auth/verification/success?already_verified=true');
        }

        // Mark email as verified
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        \Log::info('User email verified successfully', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        // Redirect to frontend with success message
        return redirect()->to($frontendUrl . '/auth/verification/success');
    }

    /**
     * Resend email verification notification
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resendVerification(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified.'
            ], 200);
        }

        $user->sendEmailVerificationNotification();

        \Log::info('Resent email verification', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        return response()->json([
            'message' => 'Verification link sent to your email.'
        ], 200);
    }
}
