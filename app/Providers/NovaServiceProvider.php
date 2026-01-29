<?php

namespace App\Providers;

use App\Nova\User;
use App\Nova\Bills;
use App\Nova\Patient;
use App\Nova\LabTests;
use Laravel\Nova\Nova;
use App\Nova\LabTestsGroup;
use Laravel\Nova\Menu\Menu;
use Illuminate\Http\Request;
use App\Nova\LabTestsResults;
use App\Nova\LabTestsCategory;
use Laravel\Nova\Menu\MenuItem;
use App\Nova\Lenses\UnpaidBills;
use App\Nova\PatientTransactions;
use Laravel\Nova\Dashboards\Main;
use App\Nova\ReferralTransactions;
use Laravel\Nova\Menu\MenuSection;
use Illuminate\Support\Facades\Gate;
use App\Nova\Dashboards\LabDashboard;
use App\Nova\LabTestsResultsTemplate;
use Illuminate\Support\Facades\Blade;
use Sereny\NovaPermissions\Nova\Role;
use Sereny\NovaPermissions\Nova\Permission;
use App\Nova\Lenses\MonthlyBillTransactions;
use App\Nova\Lenses\PartlyPaidBills;
use Laravel\Nova\NovaApplicationServiceProvider;
use KirschbaumDevelopment\NovaMail\Nova\NovaSentMail;
use KirschbaumDevelopment\NovaMail\Nova\NovaMailEvent;
use KirschbaumDevelopment\NovaMail\Nova\NovaMailTemplate;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Nova::footer(function ($request) {
            return Blade::render(string: 'nova/footer');
        });

        // Enable this to allow the custom menu
        $this->getCustomMenu();
    }

    /**
     * Register the Nova routes.
     *
     * @return void
     */
    protected function routes()
    {
        Nova::routes()
            ->withAuthenticationRoutes()
            ->withPasswordResetRoutes()
            ->register();
    }

    /**
     * Register the Nova gate.
     *
     * This gate determines who can access Nova in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewNova', function ($user) {
            // return in_array($user->email, []);
            return !is_null($user);
        });
    }

    /**
     * Get the dashboards that should be listed in the Nova sidebar.
     *
     * @return array
     */
    protected function dashboards()
    {
        return [
            // Main::make(),

            LabDashboard::make()->showRefreshButton(),
        ];
    }

    /**
     * Get the tools that should be listed in the Nova sidebar.
     *
     * @return array
     */
    public function tools()
    {
        return [
            new \Sereny\NovaPermissions\NovaPermissions(),
        ];
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Function for custom menu
     */
    private function getCustomMenu()
    {
        Nova::initialPath('/dashboards/lab-dashboard');

        Nova::mainMenu(function (Request $request) {
            return [
                // MenuSection::dashboard(Main::class),
                MenuSection::dashboard(LabDashboard::class),

                MenuSection::resource(Patient::class)
                    ->icon('user-group'),

                MenuSection::make('Laboratory Management', [
                    MenuItem::resource(LabTests::class),
                    MenuItem::resource(LabTestsResults::class),
                    MenuItem::resource(LabTestsGroup::class),
                    MenuItem::resource(LabTestsCategory::class),
                    MenuItem::resource(LabTestsResultsTemplate::class),
                ])->icon('beaker')->collapsable()->collapsedByDefault(),

                MenuSection::make('Accounting', [
                    MenuItem::resource(Bills::class),
                    MenuItem::resource(PatientTransactions::class),
                    MenuItem::resource(ReferralTransactions::class),
                ])->icon('calculator')->collapsable()->collapsedByDefault(),

                MenuSection::make('Analytics', [
                    MenuItem::lens(User::class, MonthlyBillTransactions::class),
                    MenuItem::lens(Bills::class, UnpaidBills::class),
                    MenuItem::lens(Bills::class, PartlyPaidBills::class),
                ])->icon('chart-pie')->collapsable()->collapsedByDefault()->canSee(function ($request) {
                    $userRoles = $request->user()->roles->pluck('id')->toArray();
                    return in_array(1, $userRoles) || in_array(5, $userRoles);
                }),

                MenuSection::make('Administrator', [
                    MenuItem::resource(User::class),
                    MenuItem::resource(Role::class),
                    MenuItem::resource(Permission::class),
                    MenuItem::resource(NovaSentMail::class),
                    MenuItem::resource(NovaMailTemplate::class),
                    MenuItem::resource(NovaMailEvent::class),
                ])->icon('cog')->collapsable()->collapsedByDefault()->canSee(function ($request) {
                    $userRoles = $request->user()->roles->pluck('id')->toArray();
                    return in_array(1, $userRoles) || in_array(5, $userRoles);
                }),
            ];
        });
    }
}
