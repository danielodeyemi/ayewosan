<?php

namespace App\Nova\Repeater;

use App\Models\LabTests;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Fields\Repeater\Repeatable;

class TestItem extends Repeatable
{
    /**  
     * The underlying model the repeatable represents. 
     * 
     * @var class-string
     */
    public static $model = \App\Models\LabTests::class;

    /**
     * Get the fields displayed by the repeatable.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            Select::make('Lab Tests', 'labTests')
                ->options(\App\Models\LabTests::all()->pluck('name', 'id', 'patient_price')->toArray())
                ->searchable(),
        ];
    }
}
