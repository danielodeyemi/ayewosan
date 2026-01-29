<?php

namespace App\Models;

use App\Models\LabTests;
use App\Models\ReferralTransactions;
use App\Models\PatientTransactions;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Bills extends Model
{
    use HasFactory;

    protected $casts = [
        'bill_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $fillable = [
        'patient_id',
        'bill_date',
        'bill_number',
        'total_amount',
        'discount',
        'payment_status',
        'remarks',
        'processed_by',
        'paid_amount',
        'due_amount',
    ];

    protected static function booted()
    {
        static::creating(function ($bill) {
            if (auth()->check()) {
                $bill->processed_by = auth()->id();
            }
        });

        static::created(function ($bill) {
            $bill->calculateAndSetAmounts();
        });

        static::updating(function ($bill) {
            $bill->calculateAndSetAmounts();
        });
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function patient_transactions()
    {
        return $this->hasMany(PatientTransactions::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * The labtests that belong to the Bills
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function labTests()
    {
        return $this->belongsToMany(LabTests::class, 'bills_labtests_pivot', 'bills_id', 'lab_tests_id');
    }

    public function getTotalCostAttribute()
    {
        return $this->labTests->sum('patient_price');
    }

    public function calculateAndSetAmounts()
    {
        Log::info('START calculateAndSetAmounts FROM BILLS: Calculating amounts for bill id: ' . $this->id);

        // Calculate the total cost of all lab tests
        $totalAmount = $this->getTotalCostAttribute();
        Log::info('Total amount: ' . $totalAmount);

        // Calculate the amount after discount
        $amountAfterDiscount = $totalAmount - $this->discount;

        // Check if there are any transactions
        if ($this->patient_transactions->isEmpty()) {
            // If there are no transactions, set paidAmount to 0
            $paidAmount = 0;
        } else {
            // If there are transactions, calculate the total amount of all transactions
            $paidAmount = $this->patient_transactions->sum('amount_paid');
        }
        Log::info('Paid amount: ' . $paidAmount);

        // Calculate the remaining amount
        $dueAmount = $amountAfterDiscount - $paidAmount;
        Log::info('Due amount: ' . $dueAmount);

        // Calculate the payment status
        $paymentStatus = $this->calculatePaymentStatus($dueAmount, $paidAmount);
        Log::info('Payment status: ' . $paymentStatus);

        // Update the model's attributes without saving the model
        $this->attributes['total_amount'] = $totalAmount;
        $this->attributes['paid_amount'] = $paidAmount;
        $this->attributes['due_amount'] = $dueAmount;
        $this->attributes['payment_status'] = $paymentStatus;

        Log::info('Amounts calculated for bill id: ' . $this->id);
        Log::info('END calculateAndSetAmounts FROM BILLS. Info saved to db');
    }

    public function saveWithUpdatedAmounts(array $options = [])
    {
        Log::info('Start savewithupdatedamounts from bills function');
        $this->calculateAndSetAmounts();
        Log::info('Information saved to db');
        Log::info('End savewithupdatedamounts from bills function');
        return $this->save($options);
    }

    public function calculatePaymentStatus($dueAmount, $paidAmount)
    {
        if ($dueAmount > 0 && $paidAmount > 0) {
            return 'Partly Paid';
        } elseif ($dueAmount > 0) {
            return 'Unpaid';
        } else {
            return 'Fully Paid';
        }
    }

    public function referral()
    {
        return $this->hasOne(ReferralTransactions::class);
    }

    public function labTestsResults()
    {
        return $this->hasOne(LabTestsResults::class, 'bills_id');
    }
}
