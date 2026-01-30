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
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\Image;
use Outl1ne\NovaSettings\NovaSettings;
use App\Support\EnvEditor;
use Outl1ne\NovaSettings\Models\Settings as NovaSettingModel;

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

        // Register Nova Settings fields - organized by category
        
        // Laboratory Information Settings
        NovaSettings::addSettingsFields([
            Text::make('Laboratory Name', 'lab_name')
                ->help('The name of your laboratory'),
            
            Textarea::make('Laboratory Address', 'lab_address')
                ->help('The physical address of your laboratory'),
            
            Text::make('Laboratory Phone', 'lab_phone')
                ->help('Contact phone number'),
            
            Text::make('Laboratory Email', 'lab_email')
                ->help('Email address for the laboratory'),
            
            Image::make('Laboratory Logo', 'lab_logo')
                ->disk('public')
                ->help('Logo image for the laboratory (used in invoices and reports)'),
            
            Text::make('Laboratory Website', 'lab_website')
                ->help('Website URL for the laboratory'),
            
            Textarea::make('Laboratory About', 'lab_about')
                ->help('Brief description about your laboratory'),
        ], [
            'lab_name' => 'string',
            'lab_address' => 'string',
            'lab_phone' => 'string',
            'lab_email' => 'string',
            'lab_logo' => 'string',
            'lab_website' => 'string',
            'lab_about' => 'string',
        ], 'Laboratory Information');

        // Application Settings (env-backed)
        NovaSettings::addSettingsFields([
            Text::make('Application URL', 'app_url')
                ->help('Application base URL (from .env)')
                ->resolveUsing(fn () => EnvEditor::get('APP_URL', config('app.url')))
                ->fillUsing(function ($request, $model, $attribute, $requestAttribute) {
                    $value = $request->input($requestAttribute);
                    EnvEditor::set('APP_URL', $value);
                    NovaSettingModel::updateOrCreate(['key' => 'app_url'], ['value' => $value]);
                }),

            Text::make('Application Name', 'app_name')
                ->help('The name of your application (from .env)')
                ->resolveUsing(fn () => EnvEditor::get('APP_NAME', config('app.name')))
                ->fillUsing(function ($request, $model, $attribute, $requestAttribute) {
                    $value = $request->input($requestAttribute);
                    EnvEditor::set('APP_NAME', $value);
                    // Mirror into nova_settings table for visibility
                    NovaSettingModel::updateOrCreate(['key' => 'app_name'], ['value' => $value]);
                }),

            Text::make('Application Timezone', 'app_timezone')
                ->help('Timezone for the application (from .env)')
                ->resolveUsing(fn () => EnvEditor::get('APP_TIMEZONE', config('app.timezone')))
                ->fillUsing(function ($request, $model, $attribute, $requestAttribute) {
                    $value = $request->input($requestAttribute);
                    EnvEditor::set('APP_TIMEZONE', $value);
                    NovaSettingModel::updateOrCreate(['key' => 'app_timezone'], ['value' => $value]);
                }),

            Text::make('Currency Symbol', 'currency_symbol')
                ->help('Currency symbol used for bills (from .env)')
                ->resolveUsing(fn () => EnvEditor::get('CURRENCY_SYMBOL', '₦'))
                ->fillUsing(function ($request, $model, $attribute, $requestAttribute) {
                    $value = $request->input($requestAttribute);
                    EnvEditor::set('CURRENCY_SYMBOL', $value);
                    NovaSettingModel::updateOrCreate(['key' => 'currency_symbol'], ['value' => $value]);
                }),

            Text::make('Currency Code', 'currency_code')
                ->help('ISO currency code (from .env)')
                ->resolveUsing(fn () => EnvEditor::get('CURRENCY_CODE', 'NGN'))
                ->fillUsing(function ($request, $model, $attribute, $requestAttribute) {
                    $value = $request->input($requestAttribute);
                    EnvEditor::set('CURRENCY_CODE', $value);
                    NovaSettingModel::updateOrCreate(['key' => 'currency_code'], ['value' => $value]);
                }),

            Text::make('Default Language', 'default_language')
                ->help('Default language locale for the application (from .env)')
                ->resolveUsing(fn () => EnvEditor::get('APP_LOCALE', config('app.locale')))
                ->fillUsing(function ($request, $model, $attribute, $requestAttribute) {
                    $value = $request->input($requestAttribute);
                    EnvEditor::set('APP_LOCALE', $value);
                    NovaSettingModel::updateOrCreate(['key' => 'default_language'], ['value' => $value]);
                }),
        ], [
            'app_name' => 'string',
            'app_timezone' => 'string',
            'currency_symbol' => 'string',
            'currency_code' => 'string',
            'default_language' => 'string',
        ], 'Application Settings');

        // Nova Configuration Settings
        NovaSettings::addSettingsFields([
            Text::make('Dashboard Title', 'nova_title')
                ->help('Title displayed in sidebar')
                ->default(env('NOVA_TITLE', 'Lab Manager Admin')),
            
            Textarea::make('Dashboard Footer Text', 'nova_footer_text')
                ->help('Custom footer text for dashboard')
                ->default('Laboratory Management System'),
            
        ], [
            'nova_title' => 'string',
            'nova_footer_text' => 'string',
        ], 'Dashboard Configuration');

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
            new \Outl1ne\NovaSettings\NovaSettings
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

                MenuSection::make('Settings', [
                    MenuItem::make('Laboratory Information', '/nova-settings/laboratory-information'),
                    MenuItem::make('Application Settings', '/nova-settings/application-settings'),
                    MenuItem::make('Dashboard Configuration', '/nova-settings/dashboard-configuration'),
                ])->icon('adjustments')->collapsable()->collapsedByDefault()->canSee(function ($request) {
                    $userRoles = $request->user()->roles->pluck('id')->toArray();
                    return in_array(1, $userRoles) || in_array(5, $userRoles);
                }),
            ];
        });
    }
}
