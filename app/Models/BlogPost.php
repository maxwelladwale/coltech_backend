<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'author',
        'tags',
        'published',
        'published_at',
        'views',
        'ai_generated',
    ];

    protected $casts = [
        'tags' => 'array',
        'published' => 'boolean',
        'published_at' => 'datetime',
        'ai_generated' => 'boolean',
    ];

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('published', true)
            ->where('published_at', '<=', now());
    }

    public function scopeDraft($query)
    {
        return $query->where('published', false);
    }

    // Automatically generate slug
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($post) {
            if (!$post->slug) {
                $post->slug = Str::slug($post->title);
            }
        });
    }

    // Helper methods
    public function incrementViews(): void
    {
        $this->increment('views');
    }

    public function publish(): void
    {
        $this->published = true;
        $this->published_at = now();
        $this->save();
    }

    public function unpublish(): void
    {
        $this->published = false;
        $this->save();
    }
}