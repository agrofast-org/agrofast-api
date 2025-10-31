<?php

namespace App\Models\Hr;

use App\Enums\CashOutStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CashOut extends Model
{
    use HasFactory;

    protected $table = 'cash_out';

    protected $fillable = [
        'user_id',
        'amount',
        'status',
        'rejected_for',
        'payment_proof_id',
    ];

    protected $casts = [
        'status' => CashOutStatus::class,
        'amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function paymentProof()
    {
        return $this->belongsTo(\App\Models\File\File::class, 'payment_proof_id');
    }


    public function scopePending($query)
    {
        return $query->where('status', CashOutStatus::Pending);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', CashOutStatus::Approved);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', CashOutStatus::Rejected);
    }


    public function approve(): void
    {
        $this->update(['status' => CashOutStatus::Approved]);
    }

    public function reject(?string $reason = null): void
    {
        $this->update([
            'status' => CashOutStatus::Rejected,
            'rejected_for' => $reason,
        ]);
    }

    public function isPending(): bool
    {
        return $this->status === CashOutStatus::Pending;
    }

    public function isApproved(): bool
    {
        return $this->status === CashOutStatus::Approved;
    }

    public function isRejected(): bool
    {
        return $this->status === CashOutStatus::Rejected;
    }
}
