<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabTestsGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'lab_tests_categories_id',
    ];

    public function LabTestsCategory(): BelongsTo
    {
        return $this->belongsTo(LabTestsCategory::class, 'lab_tests_categories_id');
    }

    public function LabTests(): HasMany
    {
        return $this->hasMany(LabTests::class, 'lab_tests_groups_id');
    }
}
