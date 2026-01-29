<?php

namespace App\Models;

use App\Models\Bills;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralTransactions extends Model
{
    use HasFactory;

    protected $fillable = [
        'referrer_id',
        'before_payout',
        'ref_amount',
        'type',
        'payment_method',
        'processed_by'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($transaction) {
            if (auth()->check()) {
                $transaction->processed_by = auth()->id();
                $transaction->setBeforePayout($transaction);
            }
        });

        static::updating(function ($transaction) {
            $transaction->setBeforePayout($transaction);
        });

        static::created(function ($transaction) {
            $transaction->updateAccountBalance($transaction);
        });

        static::updated(function ($transaction) {
            $transaction->updateAccountBalance($transaction);
        });

        static::deleted(function ($transaction) {
            $transaction->updateAccountBalance($transaction, true);
        });
    }

    // Each referral transaction belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function processedByUser()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // Referral transactions has many bills through patients
    // Using hasmanythrough relationship
    public function bills()
    {
        return $this->hasManyThrough(
            Bills::class,
            Patient::class,
            'referrer_id', // Foreign key on patients table...
            'patient_id', // Foreign key on bills table...
            'id', // Local key on referral transactions table...
        );
    }

    protected function setBeforePayout($transaction)
    {
        $user = User::find($transaction->referrer_id);
        $transaction->before_payout = $user->account_balance;
    }

    public function updateAccountBalance($transaction, $isDeleted = false)
    {
        $user = User::find($transaction->referrer_id);
        $originalRefAmount = $transaction->getOriginal('ref_amount');
        $newRefAmount = $transaction->ref_amount;

        if (!$isDeleted) {
            $originalType = $transaction->getOriginal('type');
            $newType = $transaction->type;

            if ($originalType == 'Credit') {
                $user->account_balance -= $originalRefAmount;
            } else {
                $user->account_balance += $originalRefAmount;
            }

            if ($newType == 'Credit') {
                $user->account_balance += $newRefAmount;
            } else {
                $user->account_balance -= $newRefAmount;
            }
        } else {
            if ($transaction->type == 'Credit') {
                $user->account_balance -= $newRefAmount;
            } else {
                $user->account_balance += $newRefAmount;
            }
        }

        $user->saveQuietly();
    }
}
