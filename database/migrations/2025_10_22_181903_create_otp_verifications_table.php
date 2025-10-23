<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->index();
            $table->string('email')->nullable();
            $table->string('otp_code');
            $table->timestamp('expires_at')->index();
            $table->boolean('verified')->default(false)->index();
            $table->timestamp('verified_at')->nullable();
            $table->string('purpose')->default('certificate_verification');
            $table->timestamps();
            
            $table->index(['phone', 'verified', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_verifications');
    }
};