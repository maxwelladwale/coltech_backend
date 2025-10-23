<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\JsonResponse;

class PackageController extends Controller
{
    /**
     * Get all packages
     * Matches: IPackageService.getPackages()
     * 
     * GET /api/packages
     */
    public function index(): JsonResponse
    {
        $packages = Package::get();
        return response()->json($packages);
    }

    /**
     * Get single package by ID
     * Matches: IPackageService.getPackageById()
     * 
     * GET /api/packages/{id}
     */
    public function show(string $id): JsonResponse
    {
        $package = Package::find($id);

        if (!$package) {
            return response()->json([
                'message' => 'Package not found'
            ], 404);
        }

        return response()->json($package);
    }
}