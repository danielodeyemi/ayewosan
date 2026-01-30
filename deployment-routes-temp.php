<?php

/*
|--------------------------------------------------------------------------
| Post-Deployment Setup Routes (TEMPORARY - REMOVE AFTER USE!)
|--------------------------------------------------------------------------
|
| These routes are for servers without SSH access to run artisan commands.
| Add these to routes/web.php ONLY during initial deployment.
| REMOVE IMMEDIATELY after running them!
|
| Security: Change the secret key to something random.
|
*/

$deploymentSecret = 'CHANGE_THIS_TO_RANDOM_STRING_12345'; // CHANGE THIS!

// Run database migrations
Route::get("/deploy-migrate-{$deploymentSecret}", function() {
    if (!app()->environment('production')) {
        return 'Only allowed in production environment';
    }
    
    try {
        Artisan::call('migrate', ['--force' => true]);
        $output = Artisan::output();
        return '<pre>Migrations completed!<br><br>' . $output . '</pre>';
    } catch (\Exception $e) {
        return '<pre>Error: ' . $e->getMessage() . '</pre>';
    }
});

// Create storage symbolic link
Route::get("/deploy-storage-link-{$deploymentSecret}", function() {
    try {
        Artisan::call('storage:link');
        return 'Storage link created successfully!';
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});

// Cache configuration
Route::get("/deploy-cache-config-{$deploymentSecret}", function() {
    try {
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:cache');
        return 'All caches created successfully!';
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});

// Clear all caches (if needed)
Route::get("/deploy-clear-cache-{$deploymentSecret}", function() {
    try {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        return 'All caches cleared successfully!';
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});

// Test database connection
Route::get("/deploy-test-db-{$deploymentSecret}", function() {
    try {
        DB::connection()->getPdo();
        $dbName = DB::connection()->getDatabaseName();
        return "Database connection successful! Connected to: {$dbName}";
    } catch (\Exception $e) {
        return 'Database connection failed: ' . $e->getMessage();
    }
});

// Check environment
Route::get("/deploy-info-{$deploymentSecret}", function() {
    $info = [
        'APP_ENV' => config('app.env'),
        'APP_DEBUG' => config('app.debug') ? 'true' : 'false',
        'APP_URL' => config('app.url'),
        'DB_CONNECTION' => config('database.default'),
        'PHP_VERSION' => phpversion(),
        'LARAVEL_VERSION' => app()->version(),
        'STORAGE_LINK_EXISTS' => is_link(public_path('storage')) ? 'Yes' : 'No',
        'STORAGE_WRITABLE' => is_writable(storage_path()) ? 'Yes' : 'No',
        'CACHE_WRITABLE' => is_writable(storage_path('framework/cache')) ? 'Yes' : 'No',
    ];
    
    return '<pre>' . print_r($info, true) . '</pre>';
});

/*
|--------------------------------------------------------------------------
| IMPORTANT: Remove all routes above after deployment is complete!
|--------------------------------------------------------------------------
*/
