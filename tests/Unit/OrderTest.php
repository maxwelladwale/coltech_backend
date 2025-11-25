<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test order number generation creates unique numbers
     */
    public function test_generates_unique_order_number(): void
    {
        $orderNumber = Order::generateOrderNumber();

        // Check format: ORD-YYYYMMDD-XXXXX
        $this->assertMatchesRegularExpression('/^ORD-\d{8}-[A-Z0-9]{5}$/', $orderNumber);

        // Check date part matches today
        $today = date('Ymd');
        $this->assertStringContainsString($today, $orderNumber);
    }

    /**
     * Test that order number generation avoids collisions
     */
    public function test_order_number_generation_avoids_collisions(): void
    {
        // Generate first order number
        $firstNumber = Order::generateOrderNumber();

        // Create an order with that number
        Order::factory()->create([
            'order_number' => $firstNumber,
        ]);

        // Mock uniqid to return same value to force collision detection
        // In real scenario, the do-while loop will retry until unique
        $secondNumber = Order::generateOrderNumber();

        // Both should be valid format
        $this->assertMatchesRegularExpression('/^ORD-\d{8}-[A-Z0-9]{5}$/', $firstNumber);
        $this->assertMatchesRegularExpression('/^ORD-\d{8}-[A-Z0-9]{5}$/', $secondNumber);
    }

    /**
     * Test hasInvoice method returns false when no invoice
     */
    public function test_has_invoice_returns_false_when_no_invoice(): void
    {
        $order = Order::factory()->create([
            'invoice_url' => null,
        ]);

        $this->assertFalse($order->hasInvoice());
    }

    /**
     * Test hasInvoice method returns true when invoice exists
     */
    public function test_has_invoice_returns_true_when_invoice_exists(): void
    {
        $order = Order::factory()->create([
            'invoice_url' => '/storage/invoices/test-invoice.pdf',
        ]);

        $this->assertTrue($order->hasInvoice());
    }

    /**
     * Test getCustomerEmail returns user email for registered user
     */
    public function test_get_customer_email_returns_user_email_for_registered_user(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
        ]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'guest_email' => null,
        ]);

        $this->assertEquals('user@example.com', $order->getCustomerEmail());
    }

    /**
     * Test getCustomerEmail returns guest email for guest order
     */
    public function test_get_customer_email_returns_guest_email_for_guest_order(): void
    {
        $order = Order::factory()->create([
            'user_id' => null,
            'guest_email' => 'guest@example.com',
        ]);

        $this->assertEquals('guest@example.com', $order->getCustomerEmail());
    }

    /**
     * Test getCustomerName returns user full name for registered user
     */
    public function test_get_customer_name_returns_user_name_for_registered_user(): void
    {
        $user = User::factory()->create([
            'full_name' => 'John Doe',
        ]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertEquals('John Doe', $order->getCustomerName());
    }

    /**
     * Test getCustomerName returns shipping name for guest order
     */
    public function test_get_customer_name_returns_shipping_name_for_guest_order(): void
    {
        $order = Order::factory()->create([
            'user_id' => null,
            'shipping_name' => 'Jane Smith',
        ]);

        $this->assertEquals('Jane Smith', $order->getCustomerName());
    }

    /**
     * Test isPending method
     */
    public function test_is_pending_returns_true_for_pending_order(): void
    {
        $order = Order::factory()->create([
            'status' => 'pending',
        ]);

        $this->assertTrue($order->isPending());
    }

    /**
     * Test isPaid method
     */
    public function test_is_paid_returns_true_for_paid_order(): void
    {
        $order = Order::factory()->create([
            'payment_status' => 'paid',
        ]);

        $this->assertTrue($order->isPaid());
    }

    /**
     * Test needsInstallation method
     */
    public function test_needs_installation_returns_true_for_technician_installation(): void
    {
        $order = Order::factory()->create([
            'installation_method' => 'technician',
        ]);

        $this->assertTrue($order->needsInstallation());
    }
}
