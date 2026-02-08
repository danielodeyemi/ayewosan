<?php

namespace App\Nova\Metrics;

use Illuminate\Support\Facades\DB;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Metrics\MetricTableRow;
use Laravel\Nova\Metrics\Table;

class MostOrderedTests extends Table
{
    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        $mostOrderedTests = DB::table('bills_labtests_pivot')
            ->join('lab_tests', 'lab_tests.id', '=', 'bills_labtests_pivot.lab_tests_id')
            ->select('lab_tests.name', 'lab_tests.code', 'lab_tests.id', DB::raw('COUNT(*) as order_count'))
            ->groupBy('lab_tests.id', 'lab_tests.name', 'lab_tests.code')
            ->orderBy('order_count', 'DESC')
            ->limit(10)
            ->get();

        $rows = $mostOrderedTests->map(function ($test) {
            return MetricTableRow::make()
                ->title($test->name . ($test->code ? ' (' . $test->code . ')' : ''))
                ->subtitle('Times Ordered: ' . $test->order_count)
                ->actions(function () use ($test) {
                    return [
                        MenuItem::link('View Test', '/resources/lab-tests/' . $test->id)
                    ];
                });
        })->all();

        return $rows;
    }

    public function name()
    {
        return 'Most Ordered Tests';
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
        return 'most-ordered-tests';
    }
}
