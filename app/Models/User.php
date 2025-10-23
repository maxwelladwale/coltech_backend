<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'password',
        'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    //Relationships
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function licenses()
    {
        return $this->hasMany(License::class);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function otpVerifications()
    {
        return $this->hasMany(OtpVerification::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // Role check
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    public function isSales(): bool
    {
        return $this->role === 'sales';
    }

    public function isSupport(): bool
    {
        return $this->role === 'support';
    }

    // Accessors & Mutators
    // Allow Filament to work with 'name' attribute while using 'full_name' in database
    public function getNameAttribute(): string
    {
        return $this->full_name ?? '';
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['full_name'] = $value;
    }


}
