<?php

namespace App\Nova\Metrics;

use App\Models\User;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Metrics\MetricTableRow;
use Laravel\Nova\Metrics\Table;

class TopReferrers extends Table
{
    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        $topReferrers = User::whereHas('referredPatients')
            ->withCount('referredBills')
            ->with('referredBills')
            ->get()
            ->map(function ($user) {
                $totalRevenue = $user->referredBills->where('payment_status', 'Fully Paid')->sum('total_amount');
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'bills_count' => $user->referred_bills_count,
                    'total_revenue' => $totalRevenue,
                ];
            })
            ->sortByDesc('total_revenue')
            ->take(10)
            ->values();

        $rows = $topReferrers->map(function ($referrer) {
            return MetricTableRow::make()
                ->title('Referrer: ' . $referrer['name'])
                ->subtitle('Total Bills: ' . $referrer['bills_count'] . ' • Total Revenue: ₦' . number_format($referrer['total_revenue'], 2))
                ->actions(function () use ($referrer) {
                    return [
                        MenuItem::link('View Profile', '/resources/users/' . $referrer['id'])
                    ];
                });
        })->all();

        return $rows;
    }

    public function name()
    {
        return 'Top Referrers (All Time)';
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
        return 'top-referrers';
    }
}
