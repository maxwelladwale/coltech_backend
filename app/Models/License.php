<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class License extends Model
{
    use HasFactory;

    protected $fillable = [
        'license_key',
        'mdvr_serial_number',
        'vehicle_registration',
        'type',
        'status',
        'activation_date',
        'expiry_date',
        'renewal_price',
        'order_id',
        'user_id',
    ];

    protected $casts = [
        'activation_date' => 'date',
        'expiry_date' => 'date',
        'renewal_price' => 'decimal:2',
    ];

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('expiry_date', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<=', now());
    }

    public function scopeExpiringIn($query, int $days)
    {
        return $query->where('expiry_date', '<=', now()->addDays($days))
            ->where('expiry_date', '>', now())
            ->where('status', 'active');
    }

    public function scopeVehicle($query, $registration)
    {
        return $query->where('vehicle_registration', $registration);
    }

    // Helper methods
    public function isExpired(): bool
    {
        return $this->expiry_date->isPast();
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && !$this->isExpired();
    }

    public function daysRemaining(): int
    {
        if ($this->isExpired()) {
            return 0;
        }
        return now()->diffInDays($this->expiry_date);
    }

    public function renew(int $months = 12): void
    {
        $this->expiry_date = $this->expiry_date->addMonths($months);
        $this->status = 'active';
        $this->save();
    }

    // Generate unique license key
    public static function generateLicenseKey(): string
    {
        return 'COLT-' . strtoupper(substr(uniqid(), -4)) . '-' . 
               strtoupper(substr(uniqid(), -4)) . '-' . 
               strtoupper(substr(uniqid(), -4));
    }
}