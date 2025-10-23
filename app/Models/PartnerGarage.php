<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PartnerGarage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'county',
        'phone',
        'email',
        'rating',
        'is_active',
        'operating_hours',
        'notes',
    ];

    protected $casts = [
        'rating' => 'decimal:1',
        'is_active' => 'boolean',
        'operating_hours' => 'array',
    ];

    // Relationships
    public function orders()
    {
        return $this->hasMany(Order::class, 'garage_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCounty($query, $county)
    {
        return $query->where('county', $county);
    }

    // Helper methods
    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function hasAvailableSlots(): bool
    {
        // Logic to check available appointment slots
        return true; // Implement based on your needs
    }
}
