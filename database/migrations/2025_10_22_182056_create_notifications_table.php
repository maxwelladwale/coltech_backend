<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()
                ->constrained()->cascadeOnDelete();
            $table->enum('type', ['email', 'sms', 'whatsapp']);
            $table->enum('status', ['pending', 'sent', 'failed'])->index();
            $table->string('recipient');
            $table->string('subject')->nullable();
            $table->text('message');
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamps();
            
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};

