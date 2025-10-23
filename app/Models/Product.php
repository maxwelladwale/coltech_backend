<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sku',
        'name',
        'category',
        'description',
        'short_description',
        'price',
        'image_url',
        'video_url',
        'in_stock',
        'stock_quantity',
        'includes_free_license',
        'license_type',
        'license_duration_months',
        'channels',
        'storage_options',
        'features',
        'specifications',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'in_stock' => 'boolean',
        'includes_free_license' => 'boolean',
        'storage_options' => 'array',
        'features' => 'array',
        'specifications' => 'array',
    ];

    // Scopes
    public function scopeInStock($query)
    {
        return $query->where('in_stock', true)->where('stock_quantity', '>', 0);
    }

    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeMdvr($query)
    {
        return $query->where('category', 'mdvr');
    }

    public function scopeCamera($query)
    {
        return $query->where('category', 'camera');
    }

    // Relationships
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function packageItems()
    {
        return $this->hasMany(PackageItem::class);
    }

    // Accessors & Mutators
    public function getPriceFormattedAttribute()
    {
        return 'KES ' . number_format($this->price, 2);
    }

    // Check if product is MDVR
    public function isMdvr(): bool
    {
        return $this->category === 'mdvr';
    }

    // Check if product is camera
    public function isCamera(): bool
    {
        return $this->category === 'camera';
    }

    // Decrease stock
    public function decreaseStock(int $quantity): void
    {
        $this->stock_quantity -= $quantity;
        if ($this->stock_quantity <= 0) {
            $this->in_stock = false;
            $this->stock_quantity = 0;
        }
        $this->save();
    }

    // Increase stock
    public function increaseStock(int $quantity): void
    {
        $this->stock_quantity += $quantity;
        $this->in_stock = true;
        $this->save();
    }
}
