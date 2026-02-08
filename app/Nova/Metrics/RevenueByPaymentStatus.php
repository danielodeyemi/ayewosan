<?php

namespace App\Nova\Metrics;

use App\Models\Bills;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;

class RevenueByPaymentStatus extends Partition
{
    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        return $this->sum($request, Bills::class, 'total_amount', 'payment_status')
            ->label(function ($value) {
                return ucfirst($value);
            });
    }

    public function name()
    {
        return 'Revenue by Payment Status';
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
        return 'revenue-by-payment-status';
    }
}
