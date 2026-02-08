<?php

namespace App\Nova\Metrics;

use App\Models\Patient;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;

class PatientsByGender extends Partition
{
    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        return $this->count($request, Patient::class, 'gender')
            ->label(function ($value) {
                return ucfirst($value);
            })
            ->colors([
                'Male' => '#3B82F6',
                'Female' => '#EC4899',
            ]);
    }

    public function name()
    {
        return 'Patients by Gender';
    }

    /**
     * Determine the amount of time the results of the metric should be cached.
     *
     * @return \DateTimeInterface|\DateInterval|float|int|null
     */
    public function cacheFor()
    {
        // return now()->addMinutes(5);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'patients-by-gender';
    }
}
