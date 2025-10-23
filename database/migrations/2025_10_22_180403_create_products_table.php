<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique()->index();
            $table->string('name');
            $table->enum('category', ['mdvr', 'camera', 'cable', 'accessory', 'installation', 'license']);
            $table->text('description');
            $table->text('short_description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('image_url')->nullable();
            $table->string('video_url')->nullable();
            $table->boolean('in_stock')->default(true);
            $table->integer('stock_quantity')->default(0);
            
            // MDVR/Camera specific fields
            $table->boolean('includes_free_license')->nullable();
            $table->enum('license_type', ['ai', 'non-ai'])->nullable();
            $table->integer('license_duration_months')->nullable();
            $table->integer('channels')->nullable();
            $table->json('storage_options')->nullable();
            $table->json('features')->nullable();
            $table->json('specifications')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['category', 'in_stock']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
