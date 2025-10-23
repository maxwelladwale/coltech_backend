<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_garages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('location');
            $table->string('county')->index();
            $table->string('phone');
            $table->string('email')->nullable();
            $table->decimal('rating', 2, 1)->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->json('operating_hours')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_garages');
    }
};
