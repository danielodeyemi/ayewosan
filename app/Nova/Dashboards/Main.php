<?php

namespace App\Nova\Dashboards;

use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Dashboards\Main as Dashboard;
use InteractionDesignFoundation\HtmlCard\HtmlCard;

class Main extends Dashboard
{
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

            (new HtmlCard())->width('2/3')->html('<h4>Current Date and Time:</h4>' . '<h1>' . date('Y-m-d' . ' :: ' . 'H:i:s') . '</h1>')
                ->withBasicStyles('border-radius: 10px; padding: 10px;'),
        ];
    }
}
