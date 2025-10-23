<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PartnerGarage;
use Illuminate\Http\JsonResponse;

class GarageController extends Controller
{
    /**
     * Get all partner garages
     * Matches: IInstallationService.getPartnerGarages()
     * 
     * GET /api/garages?county=Nairobi
     */
    public function index(Request $request): JsonResponse
    {
        $query = PartnerGarage::active();

        if ($request->has('county')) {
            $query->county($request->county);
        }

        $garages = $query->orderBy('rating', 'desc')->get();

        return response()->json($garages);
    }

    /**
     * Get single garage by ID
     * Matches: IInstallationService.getGarageById()
     * 
     * GET /api/garages/{id}
     */
    public function show(string $id): JsonResponse
    {
        $garage = PartnerGarage::find($id);

        if (!$garage) {
            return response()->json([
                'message' => 'Garage not found'
            ], 404);
        }

        return response()->json($garage);
    }
}