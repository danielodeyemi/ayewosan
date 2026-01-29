<?php

namespace App\Nova\Lenses;

use Laravel\Nova\Nova;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Lenses\Lens;
use Laravel\Nova\Fields\Number;
use App\Nova\Filters\MonthlyBillPeriod;
use Laravel\Nova\Http\Requests\LensRequest;
use Laravel\Nova\Http\Requests\NovaRequest;

class MonthlyBillTransactions extends Lens
{
    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [];

    /**
     * Get the query builder / paginator for the lens.
     *
     * @param  \Laravel\Nova\Http\Requests\LensRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return mixed
     */
    public static function query(LensRequest $request, $query)
    {
        return $request->withOrdering($request->withFilters(
            $query->whereHas('referredBills', function ($query) {
                $query->whereIn('payment_status', ['Partly Paid', 'Unpaid']);
            })
        ));
    }

    /**
     * Get the fields available to the lens.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make(Nova::__('ID'), 'id')->sortable(),
            Text::make('User Name', 'name'),
            Number::make('Number of Partly Paid or Unpaid Bills', function () {
                return $this->referredBills()
                    ->whereIn('payment_status', ['Partly Paid', 'Unpaid'])
                    ->count();
            }),
            Number::make('Total Amount of Partly Paid or Unpaid Bills', function () {
                return $this->referredBills()
                    ->whereIn('payment_status', ['Partly Paid', 'Unpaid'])
                    ->sum('total_amount');
            })->displayUsing(function ($value) {
                return number_format($value, 2);
            }),
        ];
    }

    /**
     * Get the cards available on the lens.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the filters available for the lens.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [
            new MonthlyBillPeriod('>='),
            new MonthlyBillPeriod('<='),
        ];
    }

    /**
     * Get the actions available on the lens.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return parent::actions($request);
    }

    /**
     * Get the URI key for the lens.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'monthly-bill-transactions';
    }
    
    public function name()
    {
        return 'Monthly Bill Information';
    }
}
