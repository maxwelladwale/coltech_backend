<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Package extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'recommended_for',
        'total_price',
        'discounted_price',
        'is_active',
        'image_url',
        'sort_order',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'discounted_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function items()
    {
        return $this->hasMany(PackageItem::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Helper methods
    public function hasDiscount(): bool
    {
        return $this->discounted_price && $this->discounted_price < $this->total_price;
    }

    public function getSavings(): float
    {
        if (!$this->hasDiscount()) {
            return 0;
        }
        return $this->total_price - $this->discounted_price;
    }

    public function getDiscountPercentage(): int
    {
        if (!$this->hasDiscount()) {
            return 0;
        }
        return round(($this->getSavings() / $this->total_price) * 100);
    }
}
