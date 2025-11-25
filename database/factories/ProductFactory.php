<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'sku' => $this->faker->unique()->bothify('SKU-####-???'),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'short_description' => $this->faker->sentence(),
            'category' => $this->faker->randomElement(['mdvr', 'camera', 'cable', 'accessory']),
            'price' => $this->faker->randomFloat(2, 5000, 100000),
            'stock_quantity' => $this->faker->numberBetween(0, 100),
            'in_stock' => true,
            'image_url' => $this->faker->imageUrl(640, 480, 'tech', true),
            'video_url' => $this->faker->optional()->url(),
            'features' => json_encode([
                'resolution' => '1080p',
                'storage' => '128GB',
                'warranty' => '1 year',
            ]),
            'specifications' => json_encode([
                'weight' => '500g',
                'dimensions' => '10x10x5cm',
            ]),
        ];
    }

    /**
     * Indicate that the product is out of stock
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => 0,
            'in_stock' => false,
        ]);
    }

    /**
     * Indicate that the product is inactive (soft deleted)
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'in_stock' => false,
        ]);
    }
}
