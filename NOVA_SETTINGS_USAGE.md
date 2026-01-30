<?php

/**
 * NOVA SETTINGS USAGE GUIDE
 * 
 * Settings are stored in the 'nova_settings' database table and are managed
 * from the Nova Settings page in the admin panel.
 * 
 * How to access settings in your application:
 */

// ============================================
// GETTING SETTINGS
// ============================================

// Get a single setting with optional default
$labName = nova_get_setting('lab_name');
$labName = nova_get_setting('lab_name', 'Default Lab Name');

// Get multiple settings at once
$settings = nova_get_settings(['lab_name', 'lab_email', 'lab_phone']);

// Get all settings
$allSettings = nova_get_settings();

// ============================================
// AVAILABLE SETTINGS BY CATEGORY
// ============================================

/*
LABORATORY INFORMATION
- lab_name         : Laboratory Name
- lab_address      : Physical address
- lab_phone        : Phone number
- lab_email        : Email address
- lab_logo         : Logo image path
- lab_website      : Website URL
- lab_about        : Description/About

APPLICATION SETTINGS
- app_name         : Application name (from .env APP_NAME)
- app_timezone     : Timezone (from .env APP_TIMEZONE)
- currency_symbol  : Currency symbol like $, €, ₦ (from .env CURRENCY_SYMBOL)
- currency_code    : Currency code like USD, NGN (from .env CURRENCY_CODE)
- default_language : Default language locale (from .env APP_LOCALE)

NOVA CONFIGURATION
- nova_title       : Nova sidebar title (from .env NOVA_TITLE)
- nova_footer_text : Custom footer text for Nova
*/

// ============================================
// USAGE EXAMPLES IN CODE
// ============================================

// Example 1: In a Controller
// app/Http/Controllers/ExampleController.php
/*
public function show()
{
    $labInfo = [
        'name' => nova_get_setting('lab_name'),
        'email' => nova_get_setting('lab_email'),
        'phone' => nova_get_setting('lab_phone'),
        'address' => nova_get_setting('lab_address'),
    ];
    
    return view('lab.info', $labInfo);
}
*/

// Example 2: In a Blade View
// resources/views/invoice.blade.php
/*
<div class="invoice-header">
    @if(nova_get_setting('lab_logo'))
        <img src="{{ asset('storage/' . nova_get_setting('lab_logo')) }}" alt="Logo">
    @endif
    <h1>{{ nova_get_setting('lab_name', 'Laboratory') }}</h1>
    <p>{{ nova_get_setting('lab_address') }}</p>
    <p>{{ nova_get_setting('lab_phone') }} | {{ nova_get_setting('lab_email') }}</p>
</div>

<div class="invoice-body">
    <p>Total: {{ nova_get_setting('currency_symbol') }} 5,000.00</p>
</div>
*/

// Example 3: In a Service Class
// app/Services/InvoiceService.php
/*
class InvoiceService
{
    public function generateInvoice($bill)
    {
        $labName = nova_get_setting('lab_name', 'Laboratory');
        $currency = nova_get_setting('currency_symbol', '₦');
        
        // Use settings in invoice generation
        $html = view('invoice', [
            'bill' => $bill,
            'labName' => $labName,
            'currency' => $currency,
        ])->render();
        
        return $html;
    }
}
*/

// ============================================
// DATABASE
// ============================================

/*
Settings are stored in the nova_settings table:

id          | key             | value
1           | lab_name        | Àyẹ̀wòsàn Lab
2           | lab_email       | info@lab.com
3           | currency_symbol | ₦
...

The table is automatically created during migration.
*/

// ============================================
// CACHING
// ============================================

/*
Nova Settings uses in-memory caching by default for performance.
Settings are cached for the duration of the request.

If you modify settings in code, clear the cache:
- Cache is automatically cleared when settings are updated via the Nova UI
- For programmatic updates, use:
  nova_get_settings() will retrieve fresh values
*/
