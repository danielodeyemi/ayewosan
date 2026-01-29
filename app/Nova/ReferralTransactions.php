<?php

namespace App\Nova;

use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use App\Traits\IndexQueryTrait;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Actions\ExportAsCsv;

class ReferralTransactions extends Resource
{
    /**
     * Adding the IndeQuery trait to modify shown resources by user permission
     * The file is located in App\Traits\IndexQueryTrait.php
     */
    use IndexQueryTrait;

    protected static $userForeignKey = 'referrer_id';

    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\ReferralTransactions>
     */
    public static $model = \App\Models\ReferralTransactions::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make()
                ->hideFromIndex(),

            BelongsTo::make('User', 'user', User::class)
                ->searchable()
                ->sortable(),

            Number::make('Account Balance', function () {
                return $this->user->account_balance;
            })->step(0.01)->onlyOnDetail(),

            Number::make('Before Transaction', 'before_payout', function () {
                return $this->before_payout;
            })->step(0.01)->onlyOnDetail(),

            Number::make('Amount', 'ref_amount')
                ->rules('required')
                ->step(0.01)
                ->min(0),

            Select::make('Type')
                ->options([
                    'Credit' => 'Credit',
                    'Debit' => 'Debit',
                ])
                ->rules('required')
                ->filterable(),

            Select::make('Payment Method')
                ->options([
                    'Cash' => 'Cash',
                    'Bank Transfer' => 'Bank Transfer',
                    'POS' => 'POS',
                    'Cheque' => 'Cheque',
                ])
                ->rules('required')
                ->filterable(),

            Text::make('Processed By', function () {
                return $this->processedByUser->name;
            })->onlyOnDetail(),

            // HasMany::make('Bills', 'bills', Bills::class),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [
            ExportAsCsv::make(),
        ];
    }
}
