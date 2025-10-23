<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'certificate_number',
        'type',
        'qr_code',
        'issued_to',
        'issued_date',
        'expiry_date',
        'details',
        'order_id',
        'license_id',
        'pdf_url',
    ];

    protected $casts = [
        'issued_date' => 'date',
        'expiry_date' => 'date',
        'details' => 'array',
    ];

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function license()
    {
        return $this->belongsTo(License::class);
    }

    // Helper methods
    public function isValid(): bool
    {
        if (!$this->expiry_date) {
            return true; // No expiry
        }
        return $this->expiry_date->isFuture();
    }

    // Generate unique certificate number
    public static function generateCertificateNumber(): string
    {
        return 'CERT-' . strtoupper(substr(uniqid(), -8));
    }

    // Generate unique QR code
    public static function generateQRCode(): string
    {
        return 'QR-' . uniqid();
    }
}