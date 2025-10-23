<?php

namespace Database\Seeders;

use App\Models\PartnerGarage;
use Illuminate\Database\Seeder;

class PartnerGarageSeeder extends Seeder
{
    public function run(): void
    {
        PartnerGarage::create([
            'name' => 'TechFit Auto Services',
            'location' => 'Parklands Road, Westlands, Nairobi',
            'county' => 'Nairobi',
            'phone' => '+254 712 345 678',
            'email' => 'info@techfit.co.ke',
            'rating' => 4.8,
            'is_active' => true,
            'operating_hours' => [
                'monday' => '08:00 - 18:00',
                'tuesday' => '08:00 - 18:00',
                'wednesday' => '08:00 - 18:00',
                'thursday' => '08:00 - 18:00',
                'friday' => '08:00 - 18:00',
                'saturday' => '09:00 - 15:00',
                'sunday' => 'Closed'
            ]
        ]);

        PartnerGarage::create([
            'name' => 'FleetPro Installation Center',
            'location' => 'Enterprise Road, Industrial Area, Nairobi',
            'county' => 'Nairobi',
            'phone' => '+254 723 456 789',
            'email' => 'install@fleetpro.co.ke',
            'rating' => 4.6,
            'is_active' => true,
            'operating_hours' => [
                'monday' => '07:00 - 17:00',
                'tuesday' => '07:00 - 17:00',
                'wednesday' => '07:00 - 17:00',
                'thursday' => '07:00 - 17:00',
                'friday' => '07:00 - 17:00',
                'saturday' => '08:00 - 13:00',
                'sunday' => 'Closed'
            ]
        ]);

        PartnerGarage::create([
            'name' => 'SafeDrive Installations',
            'location' => 'Mombasa Road, Near Airport',
            'county' => 'Nairobi',
            'phone' => '+254 734 567 890',
            'email' => 'info@safedrive.co.ke',
            'rating' => 4.9,
            'is_active' => true,
            'operating_hours' => [
                'monday' => '08:00 - 19:00',
                'tuesday' => '08:00 - 19:00',
                'wednesday' => '08:00 - 19:00',
                'thursday' => '08:00 - 19:00',
                'friday' => '08:00 - 19:00',
                'saturday' => '09:00 - 16:00',
                'sunday' => '10:00 - 14:00'
            ]
        ]);

        $this->command->info('✅ Created 3 partner garages');
    }
}