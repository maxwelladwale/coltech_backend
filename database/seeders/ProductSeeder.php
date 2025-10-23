<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // ================================================================
        // MDVR PRODUCTS (Exactly from mockMDVRs)
        // ================================================================
        
        Product::create([
            'sku' => 'MDVR-4CH-AI-PRO',
            'name' => 'MDVR 4-Channel AI Pro',
            'category' => 'mdvr',
            'description' => 'Professional 4-channel MDVR with advanced AI features including ADAS, DMS, and real-time driver behavior monitoring. Perfect for fleet management.',
            'short_description' => 'AI-powered 4-channel MDVR with ADAS & DMS',
            'price' => 45000.00,
            'in_stock' => true,
            'stock_quantity' => 15,
            'image_url' => '/images/mdvr-4ch-ai.jpg',
            'includes_free_license' => true,
            'license_type' => 'ai',
            'license_duration_months' => 12,
            'channels' => 4,
            'storage_options' => ['hdd', 'sd_card'],
            'features' => [
                'GPS Tracking',
                'AI Driver Monitoring',
                'ADAS (Advanced Driver Assistance)',
                'Real-time Alerts',
                '4G Connectivity',
                'Cloud Storage'
            ],
            'specifications' => [
                'Resolution' => '1080P',
                'Storage' => 'Up to 2TB HDD / 256GB SD',
                'GPS' => 'Built-in',
                'Network' => '4G LTE',
                'Operating Temp' => '-20°C to 70°C'
            ]
        ]);

        Product::create([
            'sku' => 'MDVR-8CH-AI-ELITE',
            'name' => 'MDVR 8-Channel AI Elite',
            'category' => 'mdvr',
            'description' => 'Enterprise-grade 8-channel MDVR system with full AI suite. Supports up to 8 cameras with advanced analytics and cloud integration.',
            'short_description' => 'Premium 8-channel MDVR for large vehicles',
            'price' => 75000.00,
            'in_stock' => true,
            'stock_quantity' => 8,
            'image_url' => '/images/mdvr-8ch-ai.jpg',
            'includes_free_license' => true,
            'license_type' => 'ai',
            'license_duration_months' => 12,
            'channels' => 8,
            'storage_options' => ['hdd'],
            'features' => [
                'GPS Tracking',
                'AI Driver Monitoring',
                'ADAS (Advanced Driver Assistance)',
                'Blind Spot Detection',
                'Fatigue Detection',
                '4G/5G Connectivity',
                'Cloud Storage',
                'Panic Button'
            ],
            'specifications' => [
                'Resolution' => '1080P',
                'Storage' => 'Up to 4TB HDD',
                'GPS' => 'Built-in',
                'Network' => '4G/5G LTE',
                'Operating Temp' => '-30°C to 80°C'
            ]
        ]);

        Product::create([
            'sku' => 'MDVR-4CH-STD',
            'name' => 'MDVR 4-Channel Standard',
            'category' => 'mdvr',
            'description' => 'Reliable 4-channel MDVR for basic fleet monitoring. No AI features, but includes GPS tracking and HD recording.',
            'short_description' => 'Affordable 4-channel MDVR without AI',
            'price' => 28000.00,
            'in_stock' => true,
            'stock_quantity' => 25,
            'image_url' => '/images/mdvr-4ch-std.jpg',
            'includes_free_license' => true,
            'license_type' => 'non-ai',
            'license_duration_months' => 12,
            'channels' => 4,
            'storage_options' => ['sd_card'],
            'features' => [
                'GPS Tracking',
                'HD Recording',
                '3G Connectivity',
                'Basic Alerts'
            ],
            'specifications' => [
                'Resolution' => '720P',
                'Lens' => '120° Wide Angle',
                'Waterproof' => 'IP68',
                'Operating Temp' => '-20°C to 70°C'
            ]
        ]);

        // ================================================================
        // CAMERAS (For Package Items)
        // ================================================================

        Product::create([
            'sku' => 'CAM-FRONT-1080P',
            'name' => 'Front Camera 1080P',
            'category' => 'camera',
            'description' => 'High-definition 1080P front-facing camera with wide-angle lens. Perfect for capturing road incidents and driver view.',
            'short_description' => '1080P HD front camera with wide-angle lens',
            'price' => 6500.00,
            'in_stock' => true,
            'stock_quantity' => 40,
            'image_url' => '/images/cam-front-1080p.jpg',
            'features' => [
                '1080P Full HD',
                '140° Wide Angle',
                'Night Vision',
                'Waterproof IP67'
            ],
            'specifications' => [
                'Resolution' => '1080P',
                'Lens' => '140° Wide Angle',
                'Waterproof' => 'IP67',
                'Operating Temp' => '-20°C to 70°C'
            ]
        ]);

        Product::create([
            'sku' => 'CAM-INTERIOR',
            'name' => 'Interior Camera',
            'category' => 'camera',
            'description' => 'Interior cabin camera with infrared night vision. Monitors driver behavior and passenger activity for safety and security.',
            'short_description' => 'Interior camera with IR night vision',
            'price' => 5500.00,
            'in_stock' => true,
            'stock_quantity' => 35,
            'image_url' => '/images/cam-interior.jpg',
            'features' => [
                '720P HD',
                'IR Night Vision',
                'Audio Recording',
                'Wide-Angle View'
            ],
            'specifications' => [
                'Resolution' => '720P',
                'Lens' => '120° Wide Angle',
                'IR Range' => 'Up to 5 meters',
                'Audio' => 'Built-in microphone'
            ]
        ]);

        Product::create([
            'sku' => 'CAM-SIDE-REAR-KIT',
            'name' => 'Side & Rear Camera Kit',
            'category' => 'camera',
            'description' => 'Complete side and rear camera kit for blind spot elimination. Includes 2 side cameras and 1 rear camera with mounting brackets.',
            'short_description' => 'Complete 3-camera kit for sides and rear',
            'price' => 12000.00,
            'in_stock' => true,
            'stock_quantity' => 20,
            'image_url' => '/images/cam-side-rear-kit.jpg',
            'features' => [
                '3 Cameras Included',
                '720P HD Recording',
                'Blind Spot Coverage',
                'Weatherproof Design',
                'Parking Guidelines'
            ],
            'specifications' => [
                'Resolution' => '720P',
                'Package' => '2x Side + 1x Rear',
                'Lens' => '120° Wide Angle',
                'Waterproof' => 'IP68',
                'Mounting' => 'All brackets included'
            ]
        ]);

        // ================================================================
        // ACCESSORIES (Exactly from mockAccessories)
        // ================================================================
        
        Product::create([
            'sku' => 'CABLE-EXT-10M',
            'name' => 'Extension Cable 10M',
            'category' => 'cable',
            'description' => '10-meter extension cable for camera connections',
            'short_description' => '10-meter professional-grade extension cable',
            'price' => 1500.00,
            'in_stock' => true,
            'stock_quantity' => 100,
            'image_url' => null,
            'specifications' => [
                'Length' => '10 meters',
                'Type' => 'Shielded',
                'Connector' => 'Aviation plug'
            ]
        ]);

        Product::create([
            'sku' => 'HDD-2TB',
            'name' => 'Hard Disk 2TB',
            'category' => 'accessory',
            'description' => '2TB enterprise-grade hard disk for MDVR storage',
            'short_description' => 'Enterprise HDD for continuous recording',
            'price' => 8500.00,
            'in_stock' => true,
            'stock_quantity' => 20,
            'image_url' => null,
            'specifications' => [
                'Capacity' => '2TB',
                'Type' => 'Enterprise SATA',
                'RPM' => '7200'
            ]
        ]);

        Product::create([
            'sku' => 'GPS-ANTENNA',
            'name' => 'GPS Antenna',
            'category' => 'accessory',
            'description' => 'External GPS antenna for improved signal reception',
            'short_description' => 'High-gain GPS antenna',
            'price' => 2000.00,
            'in_stock' => true,
            'stock_quantity' => 50,
            'image_url' => null,
            'specifications' => [
                'Gain' => '28dB',
                'Cable Length' => '3 meters'
            ]
        ]);

        Product::create([
            'sku' => 'SD-128GB',
            'name' => 'SD Card 128GB',
            'category' => 'accessory',
            'description' => 'High-speed 128GB SD card for continuous recording',
            'short_description' => 'Professional SD card for DVR',
            'price' => 3500.00,
            'in_stock' => true,
            'stock_quantity' => 75,
            'image_url' => null,
            'specifications' => [
                'Capacity' => '128GB',
                'Speed Class' => 'UHS-I U3',
                'Write Speed' => 'Up to 90MB/s'
            ]
        ]);

        $this->command->info('✅ Created 10 products (3 MDVRs, 3 Cameras, 4 Accessories)');
    }
}
