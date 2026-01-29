<?php

namespace App\Nova;

use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Number;
use Illuminate\Validation\Rules;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\UiAvatar;
use Laravel\Nova\Fields\MorphToMany;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Nova\Lenses\MonthlyBillTransactions;
use KirschbaumDevelopment\NovaMail\Actions\SendMail;
use KirschbaumDevelopment\NovaMail\Nova\NovaSentMail;
use Maatwebsite\LaravelNovaExcel\Actions\DownloadExcel;

class User extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\User>
     */
    public static $model = \App\Models\User::class;

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
        'id', 'name', 'email',
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
            ID::make()->sortable()
                ->hideFromIndex(),

            // UiAvatar::make()->maxWidth(50),

            Text::make('Name')
                ->sortable()
                ->rules('required', 'max:255'),

            Number::make('Referral Percentage', 'referral_percentage')
                ->min(0)->max(100)->step(0.01)
                ->sortable(),

            Text::make('Email')
                ->rules('required', 'email', 'max:254')
                ->creationRules('unique:users,email')
                ->updateRules('unique:users,email,{{resourceId}}'),

            Password::make('Password')
                ->onlyOnForms()
                ->creationRules('required', Rules\Password::defaults())
                ->updateRules('nullable', Rules\Password::defaults()),

            Number::make('Total Referral Amount', function () {
                return $this->totalReferralAmount();
            })->step(0.01),

            Number::make('Total Bills Amount', function () {
                return $this->totalBillsAmount();
            })->step(0.01),

            Number::make('Unpaid bills total', function () {
                return $this->totalUnpaidBillsAmount();
            })->step(0.01),

            Number::make('Fully Paid bills total', function () {
                return $this->totalPaidBillsAmount();
            })->step(0.01),

            Number::make('Total withdrawable amount', function () {
                return $this->withdrawableAccountBalance();
            })->step(0.01),

            HasMany::make('Referred Patients', 'referredPatients', Patient::class)
                ->collapsedByDefault(),

            HasMany::make('Referral Transactions', 'referralTransactions', ReferralTransactions::class)
                ->collapsedByDefault(),

            HasMany::make('Referred Bills', 'referredBills', Bills::class)
                ->collapsedByDefault(),

            HasMany::make('Sent Mail', 'mails', NovaSentMail::class),

            MorphToMany::make('Roles', 'roles', \Sereny\NovaPermissions\Nova\Role::class),

            MorphToMany::make('Permissions', 'permissions', \Sereny\NovaPermissions\Nova\Permission::class)->hideFromDetail(),
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
            (new MonthlyBillTransactions())->canSeeWhen(
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
            new SendMail,
            (new DownloadExcel)->withFilename('users-' . time() . '.xlsx')->withHeadings(),
        ];
    }
}
