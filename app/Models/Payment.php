<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    public const STATUSES = [
        'APPROVED' => 'Aprobado',
        'REJECTED' => 'Rechazado',
        'PENDING' => 'Pendiente',
        'IN PROCESS' => 'En proceso',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'payment_method_id',
        'amount',
        'description',
    ];

    /**
     * Get the payment method for the payment.
     */
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function getFormattedAmountAttribute(): string
    {
        return "\${$this->amount} COP";
    }
}
