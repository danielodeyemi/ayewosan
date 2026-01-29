<?php

namespace App\Nova\Dashboards;

use Laravel\Nova\Dashboard;
use Illuminate\Support\Facades\Auth;
use App\Nova\Metrics\BillsWithoutTestResult;
use App\Nova\Metrics\BillsWithoutResultTable;
use InteractionDesignFoundation\HtmlCard\HtmlCard;

class LabDashboard extends Dashboard
{
    public function name()
    {
        return 'Dashboard';
    }

    /**
     * Get the cards for the dashboard.
     *
     * @return array
     */
    public function cards()
    {
        return [
            (new HtmlCard())->width('1/3')->html('<h4>Hello;</h4>' . '<h2>' . Auth::user()->name . '!</h2>')
                ->withBasicStyles('border-radius: 10px; padding: 10px;'),

            // (new HtmlCard())->width('2/3')->html('<h4>Current Date and Time:</h4>' . '<h1>' . date('Y-m-d' . ' :: ' . 'H:i:s') . '</h1>')
            //     ->withBasicStyles('border-radius: 10px; padding: 10px;'),

            (new BillsWithoutTestResult)->canSee(function ($request) {
                $userRoles = $request->user()->roles->pluck('id')->toArray();
                return in_array(1, $userRoles) || in_array(4, $userRoles) || in_array(5, $userRoles);
            }),

            (new BillsWithoutResultTable)->width('full')->canSee(function ($request) {
                $userRoles = $request->user()->roles->pluck('id')->toArray();
                return in_array(1, $userRoles) || in_array(4, $userRoles) || in_array(5, $userRoles);
            }),
        ];
    }

    /**
     * Get the URI key for the dashboard.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'lab-dashboard';
    }
}
