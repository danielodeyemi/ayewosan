<?php

namespace App\Nova\Metrics;

use App\Models\Bills;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Metrics\MetricTableRow;
use Laravel\Nova\Metrics\Table;

class HighValueUnpaidBills extends Table
{
    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        $highValueBills = Bills::whereIn('payment_status', ['Unpaid', 'Partly Paid'])
            ->where('due_amount', '>', 5000)
            ->orderBy('due_amount', 'DESC')
            ->limit(10)
            ->get();

        $rows = $highValueBills->map(function ($bill) {
            return MetricTableRow::make()
                ->title('Bill ID: ' . $bill->id . ' • ' . ($bill->patient->name ?? 'N/A'))
                ->subtitle('Total: ₦' . number_format($bill->total_amount, 2) . ' • Due: ₦' . number_format($bill->due_amount, 2) . ' • Status: ' . $bill->payment_status)
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
        return 'High Value Unpaid Bills (₦5,000+)';
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
        return 'high-value-unpaid-bills';
    }
}
