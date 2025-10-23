<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OtpVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone',
        'email',
        'otp_code',
        'expires_at',
        'verified',
        'verified_at',
        'purpose',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    // Scopes
    public function scopeUnverified($query)
    {
        return $query->where('verified', false);
    }

    public function scopeNotExpired($query)
    {
        return $query->where('expires_at', '>', now());
    }

    // Helper methods
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return !$this->verified && !$this->isExpired();
    }

    public function verify(): void
    {
        $this->verified = true;
        $this->verified_at = now();
        $this->save();
    }

    // Generate OTP
    public static function generateOTP(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}
