<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'user_id',
        'guest_email',
        'subtotal',
        'tax',
        'shipping',
        'total',
        'status',
        'payment_status',
        'payment_method',
        'payment_transaction_id',
        'tracking_number',
        'carrier',
        'tracking_url',
        'shipping_name',
        'shipping_phone',
        'shipping_email',
        'shipping_address',
        'shipping_city',
        'shipping_county',
        'shipping_postal_code',
        'installation_method',
        'garage_id',
        'appointment_date',
        'appointment_time',
        'vehicle_registration',
        'vehicle_make',
        'vehicle_model',
        'invoice_url',
        'invoice_qr_code',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'shipping' => 'decimal:2',
        'total' => 'decimal:2',
        'appointment_date' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function garage()
    {
        return $this->belongsTo(PartnerGarage::class, 'garage_id');
    }

    public function licenses()
    {
        return $this->hasMany(License::class);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', 'pending');
    }

    // Helper methods
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function needsInstallation(): bool
    {
        return $this->installation_method === 'technician';
    }

    public function getCustomerEmail(): string
    {
        return $this->user ? $this->user->email : $this->guest_email;
    }

    public function getCustomerName(): string
    {
        return $this->user ? $this->user->full_name : $this->shipping_name;
    }

    // Static method to generate unique order number
    public static function generateOrderNumber(): string
    {
        do {
            // Generate order number: ORD-YYYYMMDD-XXXXX (e.g., ORD-20251116-A1B2C)
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));

            // Check if it already exists in database
            $exists = static::where('order_number', $orderNumber)->exists();
        } while ($exists);

        return $orderNumber;
    }

    /**
     * Generate invoice PDF for this order
     *
     * @return void
     */
    public function generateInvoice(): void
    {
        $invoiceService = app(\App\Services\InvoiceService::class);
        $invoiceData = $invoiceService->generateInvoice($this);

        // Update order with invoice details
        $this->update([
            'invoice_url' => $invoiceData['url'],
            'invoice_qr_code' => $invoiceData['qr_code'],
        ]);

        \Log::info('Invoice generated for order', [
            'order_number' => $this->order_number,
            'invoice_url' => $invoiceData['url'],
        ]);
    }

    /**
     * Check if order has an invoice
     *
     * @return bool
     */
    public function hasInvoice(): bool
    {
        return !empty($this->invoice_url);
    }
}