<?php

namespace App\Nova\Metrics;

use App\Models\Bills;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Metrics\Table;
use Laravel\Nova\Metrics\MetricTableRow;
use Laravel\Nova\Http\Requests\NovaRequest;

class BillsWithoutResultTable extends Table
{
    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        $query = Bills::doesntHave('labTestsResults')->get(['id', 'bill_date']);

        $rows = $query->map(function ($bills) {
            return MetricTableRow::make()
                ->title('Bill ID: ' . $bills->id)
                ->subtitle('Bill Date: ' . $bills->bill_date)
                ->actions(function () use ($bills) {
                    return [
                        MenuItem::link('View Bill', '/resources/bills/' . $bills->id)
                    ];
                });
        })->all();

        return $rows;
    }

    public function name()
    {
        return 'Bills With No Test Result';
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
}
