<?php

namespace App\Models;

use App\Models\Bills;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LabTests extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'lab_tests_groups_id',
        'code',
        'test_description',
        'production_cost',
        'patient_price',
    ];

    public function LabTestsGroup(): BelongsTo
    {
        return $this->belongsTo(LabTestsGroup::class, 'lab_tests_groups_id');
    }

    public function bills()
    {
        return $this->belongsToMany(Bills::class, 'bills_labtests_pivot', 'lab_tests_id', 'bills_id');
    }

    public function getPriceAttribute()
    {
        return $this->patient_price;
    }
}
