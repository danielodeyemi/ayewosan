<?php

namespace App\Nova;

use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
// use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\HasMany;
use App\Nova\PatientTransactions;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Support\Facades\Log;
use Laravel\Nova\Actions\ExportAsCsv;

class Patient extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Patient>
     */
    public static $model = \App\Models\Patient::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'name', 'patient_email',
    ];

    public static function indexQuery(NovaRequest $request, $query)
    {
        $modelName = class_basename(self::class); // Get the class name without namespace

        // If user can view any patient, show all
        // If user can only view own patients, filter by referrer_id
        if (!$request->user()->can('viewAny' . $modelName) && $request->user()->can('viewOwn' . $modelName)) {
            $query = $query->where('referrer_id', $request->user()->id);
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
            ID::make()->sortable()
                ->hideFromIndex(),

            Text::make('Name')
                ->sortable(),

            Date::make('Birth Date', 'birth_date')
                ->required()
                ->displayUsing(function ($value) {
                    return $value ? $value->format('d/m/Y') : '';
                })
                ->hideFromIndex(),

            Select::make('Gender', 'gender')
                ->required()
                ->options([
                    'Male' => 'Male',
                    'Female' => 'Female',
                ])
                ->filterable(),

            Text::make('Phone Number', 'phone_number')
                ->hideFromIndex(),

            Text::make('Patient Email', 'patient_email')
                ->hideFromIndex(),

            Textarea::make('Patient Address', 'patient_address'),

            Text::make('Password', 'password')
                ->hideFromIndex(),

            BelongsTo::make('Referred By', 'referringUser', User::class)
                ->showCreateRelationButton()
                ->searchable()
                ->filterable(),

            HasMany::make('Bills', 'bills', Bills::class)
                ->collapsedByDefault(),

            HasMany::make('Patient Transactions', 'patientTransactions', PatientTransactions::class)
                ->collapsedByDefault(),

            HasMany::make('Lab Tests Results', 'labTestsResults', LabTestsResults::class)
                ->collapsedByDefault(),
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
