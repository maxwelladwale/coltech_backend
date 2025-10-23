<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::create([
            'full_name' => 'COLTECH Admin',
            'email' => 'admin@coltech.co.ke',
            'phone' => '+254712345678',
            'password' => Hash::make('password'), // Change in production!
            'role' => 'admin',
            'email_verified_at' => now()
        ]);

        // Sales user
        User::create([
            'full_name' => 'Sales Team',
            'email' => 'sales@coltech.co.ke',
            'phone' => '+254723456789',
            'password' => Hash::make('password'),
            'role' => 'sales',
            'email_verified_at' => now()
        ]);

        // Support user
        User::create([
            'full_name' => 'Support Team',
            'email' => 'support@coltech.co.ke',
            'phone' => '+254734567890',
            'password' => Hash::make('password'),
            'role' => 'support',
            'email_verified_at' => now()
        ]);

        $this->command->info('✅ Created 3 users (admin, sales, support)');
        $this->command->warn('⚠️  Default password is "password" - CHANGE THIS IN PRODUCTION!');
    }
}

