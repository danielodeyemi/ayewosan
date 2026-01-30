@echo off
REM Production Deployment Preparation Script for Windows
REM Run this script before deploying to production

echo ================================
echo Production Deployment Preparation
echo ================================
echo.

REM Check if .env file exists
if not exist .env (
    echo Error: .env file not found!
    echo    Create .env file with production settings before running this script.
    pause
    exit /b 1
)

REM Warning prompt
echo Warning: This script will prepare your application for production deployment.
echo    Make sure you have:
echo    - Updated .env with production settings
echo    - Backed up your database
echo    - Committed all changes to Git
echo.
set /p continue="Continue? (Y/N): "
if /i not "%continue%"=="Y" (
    echo Deployment preparation cancelled.
    pause
    exit /b 1
)

echo.
echo Step 1: Clearing all caches...
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo Caches cleared
echo.

echo Step 2: Installing production dependencies...
call composer install --optimize-autoloader --no-dev --prefer-dist
if errorlevel 1 (
    echo Error: Composer install failed
    pause
    exit /b 1
)
echo Production dependencies installed
echo.

echo Step 3: Building frontend assets...
call npm run build
if errorlevel 1 (
    echo Error: Asset build failed
    pause
    exit /b 1
)
echo Assets built successfully
echo.

echo Step 4: Optimizing application...
call composer dump-autoload --optimize
echo Autoloader optimized
echo.

echo ================================
echo Deployment preparation complete!
echo ================================
echo.
echo Next steps:
echo    1. Upload files to server (excluding node_modules/, tests/, .git/)
echo    2. Upload .env file separately (set to 600 permissions)
echo    3. Modify public/index.php paths to point to Laravel app directory
echo    4. Set permissions: storage/ and bootstrap/cache/ to 775
echo    5. Run migrations: php artisan migrate --force
echo    6. Create storage link: php artisan storage:link
echo    7. Cache config: php artisan config:cache
echo    8. Cache routes: php artisan route:cache
echo    9. Cache views: php artisan view:cache
echo.
echo See DEPLOYMENT_GUIDE.md for detailed instructions
echo.
pause
