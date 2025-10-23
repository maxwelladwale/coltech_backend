<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique()->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            
            // Guest checkout support
            $table->string('guest_email')->nullable()->index();
            
            // Pricing
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('shipping', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            
            // Status tracking
            $table->enum('status', [
                'pending', 'confirmed', 'processing', 
                'shipped', 'delivered', 'cancelled'
            ])->default('pending')->index();
            
            $table->enum('payment_status', ['pending', 'paid', 'failed'])
                ->default('pending')->index();
            
            $table->enum('payment_method', ['mpesa', 'card', 'bank'])->nullable();
            $table->string('payment_transaction_id')->nullable();
            
            // Shipping address (denormalized for order history)
            $table->string('shipping_name');
            $table->string('shipping_phone');
            $table->string('shipping_email');
            $table->text('shipping_address');
            $table->string('shipping_city');
            $table->string('shipping_county');
            $table->string('shipping_postal_code')->nullable();
            
            // Installation details
            $table->enum('installation_method', ['self', 'technician'])->nullable();
            $table->foreignId('garage_id')->nullable()
                ->constrained('partner_garages')->nullOnDelete();
            $table->dateTime('appointment_date')->nullable();
            $table->string('appointment_time')->nullable();
            $table->string('vehicle_registration')->nullable();
            $table->string('vehicle_make')->nullable();
            $table->string('vehicle_model')->nullable();
            
            // Documents
            $table->string('invoice_url')->nullable();
            $table->string('invoice_qr_code')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
