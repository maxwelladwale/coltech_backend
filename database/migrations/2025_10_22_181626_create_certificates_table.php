<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->string('certificate_number')->unique()->index();
            $table->enum('type', ['installation', 'license', 'product']);
            $table->string('qr_code')->unique()->index();
            $table->string('issued_to');
            $table->date('issued_date');
            $table->date('expiry_date')->nullable();
            
            $table->json('details');
            
            $table->foreignId('order_id')->nullable()
                ->constrained()->nullOnDelete();
            $table->foreignId('license_id')->nullable()
                ->constrained()->nullOnDelete();
            
            $table->string('pdf_url')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};