<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique()->index();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('featured_image')->nullable();
            $table->string('author')->default('COLTECH Team');
            $table->json('tags')->nullable();
            $table->boolean('published')->default(false)->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->integer('views')->default(0);
            $table->boolean('ai_generated')->default(false);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['published', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
