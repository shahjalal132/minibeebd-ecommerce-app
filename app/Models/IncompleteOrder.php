<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IncompleteOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'session_id',
        'ip_address',
        'name',
        'phone',
        'address',
        'delivery_charge_id',
        'payment_method',
        'from_number',
        'transaction_id',
        'screenshot_path',
    ];

    /**
     * Get the delivery charge associated with the incomplete order.
     */
    public function deliveryCharge()
    {
        return $this->belongsTo(DeliveryCharge::class, 'delivery_charge_id');
    }

    /**
     * Scope to query incomplete orders by session and IP.
     */
    public function scopeBySessionAndIp($query, $sessionId, $ipAddress)
    {
        return $query->where('session_id', $sessionId)
            ->where('ip_address', $ipAddress);
    }
}
