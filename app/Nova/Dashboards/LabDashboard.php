<?php

namespace App\Nova\Dashboards;

use Laravel\Nova\Dashboard;
use Illuminate\Support\Facades\Auth;
use App\Nova\Metrics\BillsWithoutTestResult;
use App\Nova\Metrics\BillsWithoutResultTable;
use App\Nova\Metrics\TotalRevenue;
use App\Nova\Metrics\OutstandingAmount;
use App\Nova\Metrics\RevenueByPaymentStatus;
use App\Nova\Metrics\TestResultsByStatus;
use App\Nova\Metrics\OverdueResults;
use App\Nova\Metrics\TestsAwaitingDelivery;
use App\Nova\Metrics\NewPatients;
use App\Nova\Metrics\PatientsByGender;
use App\Nova\Metrics\PendingReferralPayouts;
use App\Nova\Metrics\CommissionPaidOut;
use App\Nova\Metrics\TopReferrers;
use App\Nova\Metrics\MostOrderedTests;
use App\Nova\Metrics\HighValueUnpaidBills;
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
        $userRoles = request()->user()->roles->pluck('id')->toArray();
        $isAdmin = in_array(1, $userRoles);
        $isLabStaff = in_array(4, $userRoles) || in_array(5, $userRoles);

        return [
            // Welcome Card
            (new HtmlCard())
                ->width('1/3')
                ->html('<h4>Hello,</h4>' . '<h2>' . Auth::user()->name . '!</h2>')
                ->withBasicStyles('border-radius: 10px; padding: 10px;'),

            // Row 1: Key Financial Metrics (Admin only)
            (new TotalRevenue)
                ->width('1/3')
                ->canSee(function () use ($isAdmin) {
                    return $isAdmin;
                }),

            (new OutstandingAmount)
                ->width('1/3')
                ->canSee(function () use ($isAdmin) {
                    return $isAdmin;
                }),

            (new PendingReferralPayouts)
                ->width('1/3')
                ->canSee(function () use ($isAdmin) {
                    return $isAdmin;
                }),

            (new NewPatients)
                ->width('1/3')
                ->canSee(function () use ($isAdmin) {
                    return $isAdmin;
                }),

            // Row 2: Operational Status (Admin & Lab Staff)
            (new RevenueByPaymentStatus)
                ->width('1/3')
                ->canSee(function () use ($isAdmin) {
                    return $isAdmin;
                }),

            (new TestResultsByStatus)
                ->width('1/3')
                ->canSee(function () use ($isAdmin, $isLabStaff) {
                    return $isAdmin || $isLabStaff;
                }),

            (new BillsWithoutTestResult)
                ->width('1/3')
                ->canSee(function () use ($isAdmin, $isLabStaff) {
                    return $isAdmin || $isLabStaff;
                }),

            (new TestsAwaitingDelivery)
                ->width('1/3')
                ->canSee(function () use ($isAdmin, $isLabStaff) {
                    return $isAdmin || $isLabStaff;
                }),

            // Row 3: Patient & Test Analytics (Admin only)
            (new PatientsByGender)
                ->width('1/2')
                ->canSee(function () use ($isAdmin) {
                    return $isAdmin;
                }),

            (new CommissionPaidOut)
                ->width('1/2')
                ->canSee(function () use ($isAdmin) {
                    return $isAdmin;
                }),

            // Row 4: Action Items - Bills Without Results (Admin & Lab Staff)
            (new BillsWithoutResultTable)
                ->width('full')
                ->canSee(function () use ($isAdmin, $isLabStaff) {
                    return $isAdmin || $isLabStaff;
                }),

            // Row 5: Action Items - Overdue Results (Admin & Lab Staff)
            (new OverdueResults)
                ->width('full')
                ->canSee(function () use ($isAdmin, $isLabStaff) {
                    return $isAdmin || $isLabStaff;
                }),

            // Row 6: Action Items - High Value Unpaid Bills (Admin only)
            (new HighValueUnpaidBills)
                ->width('full')
                ->canSee(function () use ($isAdmin) {
                    return $isAdmin;
                }),

            // Row 7: Referrer Performance (Admin only)
            (new TopReferrers)
                ->width('full')
                ->canSee(function () use ($isAdmin) {
                    return $isAdmin;
                }),

            // Row 8: Test Analytics (Admin only)
            (new MostOrderedTests)
                ->width('full')
                ->canSee(function () use ($isAdmin) {
                    return $isAdmin;
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
