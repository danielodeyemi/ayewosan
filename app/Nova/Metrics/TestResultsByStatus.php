<?php

namespace App\Nova\Metrics;

use App\Models\LabTestsResults;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;

class TestResultsByStatus extends Partition
{
    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        return $this->count($request, LabTestsResults::class, 'result_status')
            ->label(function ($value) {
                return $value;
            })
            ->colors([
                'Test Pending' => '#F59E0B',
                'Result Recorded' => '#3B82F6',
                'Result Delivered' => '#10B981',
            ]);
    }

    public function name()
    {
        return 'Test Results by Status';
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
        return 'test-results-by-status';
    }
}
