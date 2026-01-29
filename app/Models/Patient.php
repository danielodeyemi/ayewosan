<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'birth_date', 
        'gender', 
        'phone_number', 
        'patient_email', 
        'patient_address', 
        'password', 
        'referrer_id'
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function referringUser()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function patientTransactions()
    {
        return $this->hasMany(PatientTransactions::class);
    }

    public function bills()
    {
        return $this->hasMany(Bills::class);
    }

    public function labTestsResults()
    {
        return $this->hasManyThrough(
            LabTestsResults::class, // The final model we wish to access
            Bills::class, // The intermediate model that connects Patient and LabTestsResults
            'patient_id', // Foreign key on the intermediate model
            'bills_id', // Foreign key on the final model
            'id', // Local key on the Patient model
            'id' // Local key on the intermediate model
        );
    }
}
