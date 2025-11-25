<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'order_number' => Order::generateOrderNumber(),
            'user_id' => null, // Guest order by default
            'guest_email' => $this->faker->email(),
            'subtotal' => $this->faker->randomFloat(2, 1000, 50000),
            'tax' => $this->faker->randomFloat(2, 100, 5000),
            'shipping' => $this->faker->randomFloat(2, 0, 2000),
            'total' => function (array $attributes) {
                return $attributes['subtotal'] + $attributes['tax'] + $attributes['shipping'];
            },
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled']),
            'payment_status' => $this->faker->randomElement(['pending', 'paid', 'failed']),
            'payment_method' => $this->faker->randomElement(['mpesa', 'card', 'bank']),
            'payment_transaction_id' => $this->faker->optional()->uuid(),
            'shipping_name' => $this->faker->name(),
            'shipping_phone' => $this->faker->phoneNumber(),
            'shipping_email' => $this->faker->email(),
            'shipping_address' => $this->faker->streetAddress(),
            'shipping_city' => $this->faker->city(),
            'shipping_county' => $this->faker->randomElement(['Nairobi', 'Mombasa', 'Kisumu', 'Nakuru']),
            'shipping_postal_code' => $this->faker->optional()->postcode(),
            'installation_method' => $this->faker->randomElement(['self', 'technician']),
            'garage_id' => null,
            'appointment_date' => $this->faker->optional()->dateTimeBetween('now', '+30 days'),
            'appointment_time' => $this->faker->optional()->time(),
            'vehicle_registration' => $this->faker->optional()->bothify('K?? ###?'),
            'vehicle_make' => $this->faker->optional()->randomElement(['Toyota', 'Nissan', 'Honda', 'Mazda']),
            'vehicle_model' => $this->faker->optional()->word(),
            'invoice_url' => null,
            'invoice_qr_code' => null,
        ];
    }

    /**
     * Indicate that the order belongs to a registered user
     */
    public function forUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => User::factory(),
            'guest_email' => null,
        ]);
    }

    /**
     * Indicate that the order is pending
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);
    }

    /**
     * Indicate that the order is paid
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'paid',
        ]);
    }

    /**
     * Indicate that the order has an invoice
     */
    public function withInvoice(): static
    {
        return $this->state(fn (array $attributes) => [
            'invoice_url' => '/storage/invoices/' . $attributes['order_number'] . '-invoice.pdf',
            'invoice_qr_code' => url('/invoices/' . $attributes['order_number']),
        ]);
    }
}
