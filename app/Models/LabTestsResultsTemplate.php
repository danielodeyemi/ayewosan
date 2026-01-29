<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabTestsResultsTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'lab_tests_results_id',
        'name',
        'template_content',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function labTestsResults()
    {
        return $this->hasMany(LabTestsResults::class);
    }
}
