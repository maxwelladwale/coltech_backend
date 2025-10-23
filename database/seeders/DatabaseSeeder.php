<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Starting COLTECH database seeding...');
        $this->command->newLine();

        $this->call([
            UserSeeder::class,
            ProductSeeder::class,
            PartnerGarageSeeder::class,
            PackageSeeder::class,
        ]);

        $this->command->newLine();
        $this->command->info('🎉 Database seeded successfully!');
        $this->command->newLine();
        
        $this->command->table(
            ['Resource', 'Count'],
            [
                ['Users', '3'],
                ['Products', '10'],
                ['Partner Garages', '3'],
                ['Packages', '3'],
            ]
        );
    }
}