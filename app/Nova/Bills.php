<?php

namespace App\Nova;

use Laravel\Nova\Fields\ID;
use NovaAttachMany\AttachMany;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use App\Nova\Lenses\UnpaidBills;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\BelongsTo;
use App\Nova\Actions\DownloadBills;
use App\Nova\Lenses\PartlyPaidBills;
use App\Nova\Actions\GenerateInvoice;
use Laravel\Nova\Actions\ExportAsCsv;
use Laravel\Nova\Fields\BelongsToMany;
use App\Nova\Actions\UpdateBillAmounts;
use Laravel\Nova\Http\Requests\NovaRequest;
use Maatwebsite\LaravelNovaExcel\Actions\DownloadExcel;

class Bills extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Bills>
     */
    public static $model = \App\Models\Bills::class;

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

    public static function indexQuery(NovaRequest $request, $query)
    {
        $modelName = class_basename(self::class); // Get the class name without namespace

        // If user can view any bill, show all
        // If user can only view own bills, filter by referrer_id
        if (!$request->user()->can('viewAny' . $modelName) && $request->user()->can('viewOwn' . $modelName)) {
            $query = $query->join('patients', 'patients.id', '=', 'bills.patient_id')
                ->where('patients.referrer_id', $request->user()->id)
                ->select('bills.*'); // Select columns from Bills table
        }

        return $query;
    }

    public static function fillFields(NovaRequest $request, $model, $fields)
    {
        $filledFields = parent::fillFields($request, $model, $fields);

        // Calculate and set the amounts
        $model->calculateAndSetAmounts();

        return $filledFields;
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
            ID::make('Bill Number', 'id')->sortable(),

            BelongsTo::make('Patient', 'patient', Patient::class)
                ->searchable(),

            DateTime::make('Bill Date', 'bill_date')
                ->default(now())
                ->sortable(),

            Textarea::make('Remarks', 'remarks')
                ->nullable(),

            Number::make('Total Amount Payable', 'total_amount')
                ->default($this->getTotalCostAttribute())
                ->step(0.01)
                ->exceptOnForms(),

            Number::make('Amount Paid', 'paid_amount')
                ->step(0.01)
                ->exceptOnForms(),

            Number::make('Discount', 'discount')
                ->min(0)
                ->step(0.01)
                ->default(0)
                ->readonly(function ($request) {
                    return $request->user()->id !== 1;
                })
                ->hideFromIndex(),

            Number::make('Amount Due', 'due_amount')
                ->step(0.01)
                ->onlyOnDetail(),

            Select::make('Payment Status', 'payment_status')
                ->options([
                    'Unpaid' => 'Unpaid',
                    'Partly Paid' => 'Partly Paid',
                    'Fully Paid' => 'Fully Paid',
                ])
                ->default('Unpaid')
                ->exceptOnForms()
                ->filterable()
                ->sortable(),

            BelongsTo::make('Processed By', 'processedBy', User::class)
                ->default(function ($request) {
                    return $request->user()->id;
                })
                ->onlyOnDetail()
                ->readonly(),

            DateTime::make('Created At', 'created_at')
                ->onlyOnDetail()
                ->readonly(),

            DateTime::make('Updated At', 'updated_at')
                ->onlyOnDetail()
                ->readonly(),

            BelongsToMany::make('Lab Tests', 'labTests', LabTests::class)
                ->exceptOnForms(),

            AttachMany::make('Lab Tests', 'labTests', LabTests::class)
                ->onlyOnForms()
                ->height('200px')
                ->withSubtitles()
                ->showCounts(),

            HasMany::make('Patient Transaction', 'patient_transactions', PatientTransactions::class)
                ->nullable(),

            HasOne::make('Lab Test Results', 'labTestsResults', LabTestsResults::class)
                ->onlyOnDetail(),
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
        return [
            (new UnpaidBills())->canSeeWhen(
                'viewAnyBills',
                Bills::class
            ),

            (new PartlyPaidBills())->canSeeWhen(
                'viewAnyBills',
                Bills::class
            ),
        ];
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
            new UpdateBillAmounts,
            ExportAsCsv::make()->onlyOnIndex(),
            (new DownloadExcel)->withHeadings()->withFilename('bills-' . time() . '.xlsx')->onlyOnIndex(),
            (new DownloadBills)->withHeadings()->withFilename('bills-' . time() . '.pdf')->withWriterType(\Maatwebsite\Excel\Excel::DOMPDF)->onlyOnIndex(),
            (new GenerateInvoice())->showInline(),
        ];
    }
}
