<?php
// ============================================================================
// COLTECH API ROUTES
// routes/api.php
// ============================================================================

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\PackageController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\GarageController;
use App\Http\Controllers\API\LicenseController;
use App\Http\Controllers\API\CertificateController;
use App\Http\Controllers\API\BlogController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes match your Next.js TypeScript service interfaces exactly
| All routes are prefixed with /api automatically
|
*/

// ============================================================================
// PUBLIC ROUTES (No authentication required)
// ============================================================================

// Health Check
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'COLTECH API',
        'version' => '1.0.0',
        'timestamp' => now()->toIso8601String()
    ]);
});

// ----------------------------------------------------------------------------
// PRODUCTS
// ----------------------------------------------------------------------------
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/mdvrs', [ProductController::class, 'mdvrs']);
    Route::get('/cameras', [ProductController::class, 'cameras']);
    Route::get('/search', [ProductController::class, 'search']);
    Route::get('/{id}', [ProductController::class, 'show']);
    Route::get('/{id}/stock', [ProductController::class, 'checkStock']);
});

// ----------------------------------------------------------------------------
// PACKAGES
// ----------------------------------------------------------------------------
Route::prefix('packages')->group(function () {
    Route::get('/', [PackageController::class, 'index']);
    Route::get('/{id}', [PackageController::class, 'show']);
});

// ----------------------------------------------------------------------------
// ORDERS
// ----------------------------------------------------------------------------
Route::prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'index']);
    Route::post('/', [OrderController::class, 'store']);
    Route::post('/track', [OrderController::class, 'track']); // Guest order tracking
    Route::get('/{id}', [OrderController::class, 'show']);
    
    // Admin only (add middleware later)
    Route::patch('/{id}/status', [OrderController::class, 'updateStatus']);
});

// ----------------------------------------------------------------------------
// PARTNER GARAGES
// ----------------------------------------------------------------------------
Route::prefix('garages')->group(function () {
    Route::get('/', [GarageController::class, 'index']);
    Route::get('/{id}', [GarageController::class, 'show']);
});

// ----------------------------------------------------------------------------
// LICENSES
// ----------------------------------------------------------------------------
Route::prefix('licenses')->group(function () {
    Route::get('/vehicle/{registration}', [LicenseController::class, 'getByVehicle']);
    Route::get('/check/{registration}', [LicenseController::class, 'checkStatus']);
    Route::get('/renewal-price', [LicenseController::class, 'getRenewalPrice']);
    Route::post('/activate', [LicenseController::class, 'activate']);
    Route::post('/{id}/renew', [LicenseController::class, 'renew']);
});

// ----------------------------------------------------------------------------
// CERTIFICATE VERIFICATION
// ----------------------------------------------------------------------------
Route::prefix('certificates')->group(function () {
    Route::post('/send-otp', [CertificateController::class, 'sendOtp']);
    Route::post('/verify-otp', [CertificateController::class, 'verifyOtp']);
    Route::post('/verify-qr', [CertificateController::class, 'verifyQr']);
    Route::post('/generate', [CertificateController::class, 'generate']);
});

// ----------------------------------------------------------------------------
// BLOG
// ----------------------------------------------------------------------------
Route::prefix('blog')->group(function () {
    Route::get('/', [BlogController::class, 'index']);
    Route::get('/recent', [BlogController::class, 'recent']);
    Route::get('/{slug}', [BlogController::class, 'show']);
});

// ============================================================================
// AUTHENTICATED ROUTES (Require login)
// ============================================================================

Route::middleware('auth:sanctum')->group(function () {
    
    // User Profile
    Route::get('/user', function (Illuminate\Http\Request $request) {
        return $request->user();
    });
    
    // My Orders (authenticated user)
    Route::get('/my-orders', [OrderController::class, 'index']);
    
    // My Licenses
    Route::get('/my-licenses', function (Illuminate\Http\Request $request) {
        return $request->user()->licenses()->with('order')->get();
    });
});

// ============================================================================
// ADMIN ROUTES (Require admin role)
// ============================================================================

Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    
    // Product Management
    Route::apiResource('products', ProductController::class)
        ->except(['index', 'show']); // index & show are public
    
    // Order Management
    Route::get('/orders/pending', [OrderController::class, 'index']);
    Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);
    
    // License Management
    Route::get('/licenses', function () {
        return \App\Models\License::with(['user', 'order'])->paginate(20);
    });
    
    // Statistics
    Route::get('/stats', function () {
        return response()->json([
            'totalOrders' => \App\Models\Order::count(),
            'pendingOrders' => \App\Models\Order::pending()->count(),
            'totalRevenue' => \App\Models\Order::paid()->sum('total'),
            'activeLicenses' => \App\Models\License::active()->count(),
            'products' => \App\Models\Product::count(),
        ]);
    });
});

/*
|--------------------------------------------------------------------------
| Route List for Reference
|--------------------------------------------------------------------------
|
| GET    /api/health                           - Health check
| 
| GET    /api/products                         - Get all products
| GET    /api/products/mdvrs                   - Get MDVR products
| GET    /api/products/cameras                 - Get camera products
| GET    /api/products/search?q=GPS            - Search products
| GET    /api/products/{id}                    - Get product by ID
| GET    /api/products/{id}/stock              - Check stock
| 
| GET    /api/packages                         - Get packages
| GET    /api/packages/{id}                    - Get package by ID
| 
| GET    /api/orders                           - Get orders
| POST   /api/orders                           - Create order
| POST   /api/orders/track                     - Track order (guest)
| GET    /api/orders/{id}                      - Get order by ID
| PATCH  /api/orders/{id}/status               - Update order status
| 
| GET    /api/garages                          - Get garages
| GET    /api/garages?county=Nairobi           - Filter by county
| GET    /api/garages/{id}                     - Get garage by ID
| 
| GET    /api/licenses/vehicle/{registration}  - Get license by vehicle
| GET    /api/licenses/check/{registration}    - Check license status
| GET    /api/licenses/renewal-price?type=ai   - Get renewal price
| POST   /api/licenses/activate                - Activate license
| POST   /api/licenses/{id}/renew              - Renew license
| 
| POST   /api/certificates/send-otp            - Send OTP
| POST   /api/certificates/verify-otp          - Verify OTP
| POST   /api/certificates/verify-qr           - Verify QR code
| POST   /api/certificates/generate            - Generate certificate
| 
| GET    /api/blog                             - Get blog posts
| GET    /api/blog/recent?limit=5              - Get recent posts
| GET    /api/blog/{slug}                      - Get blog post by slug
|
*/