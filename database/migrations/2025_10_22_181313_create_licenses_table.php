<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->string('license_key')->unique()->index();
            $table->string('mdvr_serial_number')->nullable();
            $table->string('vehicle_registration')->index();
            $table->enum('type', ['ai', 'non-ai']);
            $table->enum('status', ['active', 'expired', 'suspended'])
                ->default('active')->index();
            
            $table->date('activation_date');
            $table->date('expiry_date')->index();
            $table->decimal('renewal_price', 10, 2);
            
            $table->foreignId('order_id')->nullable()
                ->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()
                ->constrained()->nullOnDelete();
            
            $table->timestamps();
            
            $table->index(['vehicle_registration', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
