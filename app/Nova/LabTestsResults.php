<?php

namespace App\Nova;

use App\Nova\Actions\PrintTestResult;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Textarea;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Fields\BelongsTo;
use Emilianotisato\NovaTinyMCE\NovaTinyMCE;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Actions\ExportAsCsv;

class LabTestsResults extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\LabTestsResults>
     */
    public static $model = \App\Models\LabTestsResults::class;

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

        // If user can view any result, show all
        // If user can only view own results, filter by referrer_id
        if (!$request->user()->can('viewAny' . $modelName) && $request->user()->can('viewOwn' . $modelName)) {
            $query = $query->join('bills', 'bills.id', '=', 'lab_tests_results.bills_id')
                ->join('patients', 'patients.id', '=', 'bills.patient_id')
                ->where('patients.referrer_id', $request->user()->id)
                ->select('lab_tests_results.*', 'lab_tests_results.id as id'); // Select columns from LabTestsResults table
        }

        return $query;
    }

    public static function relatableBills(NovaRequest $request, $query)
    {
        if ($request->user()->can('viewOwnBills')) {
            $billIds = DB::table('bills')
                ->join('patients', 'patients.id', '=', 'bills.patient_id')
                ->where('patients.referrer_id', $request->user()->id)
                ->pluck('bills.id');

            $query = $query->whereIn('id', $billIds);
        }

        return $query;
    }

    /**
     * Determine if the current user can view the given resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function authorizedToView(Request $request)
    {
        $user = $request->user();
        $modelName = class_basename(self::class);

        // Check if the user has the 'viewAny' permission or the 'viewOwn' permission and the resource belongs to them
        if ($user->can('viewAny' . $modelName) || ($user->can('viewOwn' . $modelName) && $this->bills->patient->referrer_id == $user->id)) {
            return true;
        }

        return false;
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
                ->sortable()
                ->hideFromIndex(),

            // BelongsTo::make('Patient', 'patient', Patient::class)
            // ->searchable(),

            BelongsTo::make('Bill Number', 'bills', Bills::class)
                ->searchable(),

            DateTime::make('Result Date', 'result_date'),

            DateTime::make('Delivery Date and Time', 'delivery_date_time')
                ->hideFromIndex(),

            BelongsTo::make('Performed By', 'performedBy', User::class)
                ->searchable()
                ->hideFromIndex(),

            BelongsTo::make('Delivered By', 'deliveredBy', User::class)
                ->searchable()
                ->hideFromIndex(),

            NovaTinyMCE::make(__('Result Content'), 'result_content')
                ->rules('required')
                ->options([
                    'templates' => \App\Models\LabTestsResultsTemplate::all()->map(function ($template) {
                        return [
                            'title' => $template->name,
                            'description' => $template->description,
                            'content' => $template->template_content,
                        ];
                    })->toArray(),
                ]),

            Textarea::make('Result Remarks', 'report_remarks'),

            Select::make('Result Status')
                ->options([
                    'Test Pending' => 'Test Pending',
                    'Result Recorded' => 'Result Recorded',
                    'Result Delivered' => 'Result Delivered',
                ])
                ->filterable()
                ->sortable(),
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
            new PrintTestResult,
            ExportAsCsv::make(),
        ];
    }
}
