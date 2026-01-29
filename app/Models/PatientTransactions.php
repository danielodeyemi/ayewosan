<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Bills;


class PatientTransactions extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id', 
        'bill_id', 
        'paid_on', 
        'amount_paid', 
        'payment_method', 
        'processed_by'
    ];

    protected $casts = [
        'paid_on' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($transaction) {
            if (auth()->check()) {
                $transaction->processed_by = auth()->id();
            }
        });

        static::saved(function ($transaction) {
            $transaction->bills->saveWithUpdatedAmounts();
        });
    
        static::deleted(function ($transaction) {
            $transaction->bills->saveWithUpdatedAmounts();
        });
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function bills()
    {
        return $this->belongsTo(Bills::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
