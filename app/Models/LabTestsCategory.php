<?php

namespace App\Models;

use Laravel\Nova\Fields\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabTestsCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function LabTestsGroups()
    {
        return $this->hasMany(LabTestsGroup::class, 'lab_tests_categories_id');
    }
}
