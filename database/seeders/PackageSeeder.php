<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\PackageItem;
use App\Models\Product;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        // ================================================================
        // Fleet Safety Package
        // ================================================================
        $package1 = Package::create([
            'name' => 'Fleet Safety Package',
            'description' => 'Comprehensive safety solution with AI monitoring for commercial fleets. Includes MDVR, cameras, and all accessories needed for complete vehicle coverage.',
            'recommended_for' => 'PSVs, Trucks, Commercial Fleets',
            'total_price' => 65000.00,
            'discounted_price' => 58500.00, // 10% discount
            'is_active' => true,
            'sort_order' => 1
        ]);

        // Add items
        $mdvr4ch = Product::where('sku', 'MDVR-4CH-AI-PRO')->first();
        $frontCam = Product::where('sku', 'CAM-FRONT-1080P')->first();
        $interiorCam = Product::where('sku', 'CAM-INTERIOR')->first();

        PackageItem::create([
            'package_id' => $package1->id,
            'product_id' => $mdvr4ch->id,
            'quantity' => 1
        ]);

        PackageItem::create([
            'package_id' => $package1->id,
            'product_id' => $frontCam->id,
            'quantity' => 2
        ]);

        PackageItem::create([
            'package_id' => $package1->id,
            'product_id' => $interiorCam->id,
            'quantity' => 1
        ]);

        // ================================================================
        // Asset Tracking Package
        // ================================================================
        $package2 = Package::create([
            'name' => 'Asset Tracking Package',
            'description' => 'Essential tracking solution for asset monitoring. GPS-enabled with basic recording capabilities, perfect for logistics and delivery services.',
            'recommended_for' => 'Logistics, Delivery, Personal Vehicles',
            'total_price' => 35000.00,
            'discounted_price' => 32000.00,
            'is_active' => true,
            'sort_order' => 2
        ]);

        $mdvrStd = Product::where('sku', 'MDVR-4CH-STD')->first();

        PackageItem::create([
            'package_id' => $package2->id,
            'product_id' => $mdvrStd->id,
            'quantity' => 1
        ]);

        PackageItem::create([
            'package_id' => $package2->id,
            'product_id' => $frontCam->id,
            'quantity' => 1
        ]);

        // ================================================================
        // Total Operations Package
        // ================================================================
        $package3 = Package::create([
            'name' => 'Total Operations Package',
            'description' => 'Complete fleet management solution with 8-channel MDVR and full camera coverage. Enterprise-grade system for maximum visibility and control.',
            'recommended_for' => 'Large Trucks, Buses, Heavy Equipment',
            'total_price' => 95000.00,
            'discounted_price' => 85000.00,
            'is_active' => true,
            'sort_order' => 3
        ]);

        $mdvr8ch = Product::where('sku', 'MDVR-8CH-AI-ELITE')->first();
        $sideRearCam = Product::where('sku', 'CAM-SIDE-REAR-KIT')->first();

        PackageItem::create([
            'package_id' => $package3->id,
            'product_id' => $mdvr8ch->id,
            'quantity' => 1
        ]);

        PackageItem::create([
            'package_id' => $package3->id,
            'product_id' => $frontCam->id,
            'quantity' => 2
        ]);

        PackageItem::create([
            'package_id' => $package3->id,
            'product_id' => $interiorCam->id,
            'quantity' => 2
        ]);

        PackageItem::create([
            'package_id' => $package3->id,
            'product_id' => $sideRearCam->id,
            'quantity' => 1
        ]);

        $this->command->info('✅ Created 3 packages with items');
    }
}
