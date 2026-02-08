<?php

namespace App\Nova\Metrics;

use App\Models\Bills;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Metrics\MetricTableRow;
use Laravel\Nova\Metrics\Table;
use Laravel\Nova\Nova;

class OverdueResults extends Table
{
    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        $bills = Bills::doesntHave('labTestsResults')
            ->where('bill_date', '<', now()->subDays(7))
            ->latest('bill_date')
            ->limit(10)
            ->get();

        $rows = $bills->map(function ($bill) {
            return MetricTableRow::make()
                ->title('Bill ID: ' . $bill->id)
                ->subtitle('Patient: ' . ($bill->patient->name ?? 'N/A') . ' • Bill Date: ' . $bill->bill_date->format('Y-m-d') . ' • Days Overdue: ' . now()->diffInDays($bill->bill_date))
                ->actions(function () use ($bill) {
                    return [
                        MenuItem::link('View Bill', '/resources/bills/' . $bill->id)
                    ];
                });
        })->all();

        return $rows;
    }

    public function name()
    {
        return 'Overdue Test Results (7+ Days)';
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
        return 'overdue-results';
    }
}
