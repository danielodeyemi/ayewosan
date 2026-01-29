<?php

namespace App\Nova;

use App\Models\Bills;
use App\Models\Patient;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use App\Traits\IndexQueryTrait;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\BelongsTo;
use Illuminate\Support\Facades\Log;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Actions\ExportAsCsv;

class PatientTransactions extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\PatientTransactions>
     */
    public static $model = \App\Models\PatientTransactions::class;

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
     * Adding the IndexQuery trait to modify shown resources by user permission
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        $modelName = class_basename(self::class); // Get the class name without namespace

        // If user can view any transaction, show all
        // If user can only view own transactions, filter by referrer_id
        if (!$request->user()->can('viewAny' . $modelName) && $request->user()->can('viewOwn' . $modelName)) {
            $query = $query->join('patients', 'patients.id', '=', 'patient_transactions.patient_id')
                ->where('patients.referrer_id', $request->user()->id)
                ->select('patient_transactions.*'); // Select columns from PatientTransactions table
        }

        return $query;
    }

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

            //Originally working code
            // BelongsTo::make('Patient')
            //     ->searchable(),

            // BelongsTo::make('Bills', 'bills', Bills::class)
            //     ->readonly(),

            BelongsTo::make('Patient', 'patient', \App\Nova\Patient::class)
                ->searchable()
                ->default(function ($request) {
                    if ($request->viaResource === 'bills') {
                        $bill = \App\Models\Bills::find($request->viaResourceId);
                        return $bill ? $bill->patient_id : null;
                    } elseif ($request->viaResource === 'patients') {
                        return $request->viaResourceId;
                    }
                })
                ->sortable(),

            BelongsTo::make('Bills', 'bills', \App\Nova\Bills::class)
                ->default(function ($request) {
                    if ($request->viaResource === 'bills') {
                        return $request->viaResourceId;
                    } elseif ($request->viaResource === 'patients') {
                        $patient = \App\Models\Patient::find($request->viaResourceId);
                        $bill = $patient->bills()->first();
                        return $bill ? $bill->id : null;
                    }
                })
                ->sortable(),

            DateTime::make('Paid On', 'paid_on')
                ->default(now())
                ->sortable()
                ->rules('required'),

            Number::make('Amount Paid', 'amount_paid')
                ->min(0)
                ->step(0.01)
                ->default(0)
                ->rules('required'),

            Select::make('Payment Method', 'payment_method')
                ->options([
                    'Cash' => 'Cash',
                    'P.O.S.' => 'P.O.S.',
                    'Monthly Bill' => 'Monthly Bill',
                ])
                ->rules('required')
                ->filterable(),

            BelongsTo::make('Processed By', 'processedBy', User::class)
                ->default(function ($request) {
                    return $request->user()->id;
                })
                ->onlyOnDetail()
                ->readonly(),
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
