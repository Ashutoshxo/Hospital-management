<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'amount',
        'ccavenue_transaction_id',
        'payment_status',
        'payment_date'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime'
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function isSuccessful(): bool
    {
        return $this->payment_status === 'success';
    }

    public function markAsSuccess(string $transactionId): void
    {
        $this->update([
            'payment_status' => 'success',
            'ccavenue_transaction_id' => $transactionId,
            'payment_date' => now()
        ]);
    }

    public function markAsFailed(string $transactionId = null): void
    {
        $this->update([
            'payment_status' => 'failed',
            'ccavenue_transaction_id' => $transactionId,
            'payment_date' => now()
        ]);
    }
}