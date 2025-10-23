<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /**
     * Get all products with optional filters
     * Matches: IProductService.getProducts()
     * 
     * GET /api/products?category=mdvr&inStock=true&search=AI
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::query();

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Filter by stock status
        if ($request->has('inStock')) {
            $inStock = filter_var($request->inStock, FILTER_VALIDATE_BOOLEAN);
            $query->where('in_stock', $inStock);
        }

        // Search by name or description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('created_at', 'desc')->get();

        return response()->json($products);
    }

    /**
     * Get single product by ID
     * Matches: IProductService.getProductById()
     * 
     * GET /api/products/{id}
     */
    public function show(string $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json($product);
    }

    /**
     * Get MDVR products only
     * Matches: IProductService.getMDVRProducts()
     * 
     * GET /api/products/mdvr
     */
    public function mdvrs(): JsonResponse
    {
        $mdvrs = Product::mdvr()->inStock()->get();
        return response()->json($mdvrs);
    }

    /**
     * Get camera products only
     * Matches: IProductService.getCameras()
     * 
     * GET /api/products/cameras
     */
    public function cameras(): JsonResponse
    {
        $cameras = Product::camera()->inStock()->get();
        return response()->json($cameras);
    }

    /**
     * Search products
     * Matches: IProductService.searchProducts()
     * 
     * GET /api/products/search?q=GPS
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('q', '');

        $products = Product::where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->orWhereJsonContains('features', $query)
            ->inStock()
            ->get();

        return response()->json($products);
    }

    /**
     * Check stock availability
     * Matches: IProductService.checkStock()
     * 
     * GET /api/products/{id}/stock
     */
    public function checkStock(string $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json([
            'available' => $product->in_stock && $product->stock_quantity > 0,
            'quantity' => $product->stock_quantity
        ]);
    }
}

