# Production Deployment Guide for Shared Hosting

## Pre-Deployment Checklist

### 1. Security Hardening

#### ✅ Environment Configuration
- [ ] Set `APP_ENV=production` in `.env`
- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Generate new `APP_KEY` (or keep existing secure one)
- [ ] Update `APP_URL` to your production domain
- [ ] Remove all development/debug packages

#### ✅ Database Security
- [ ] Use strong database password
- [ ] Limit database user privileges (no DROP, ALTER on production)
- [ ] Update database credentials in `.env`
- [ ] Backup existing database before deployment

#### ✅ File Permissions
- [ ] `.env` file: 600 (read/write for owner only)
- [ ] `storage/` and `bootstrap/cache/`: 775
- [ ] All other files: 644
- [ ] Directories: 755

### 2. Performance Optimization

Run these commands before deployment:
```bash
# Install production dependencies only
composer install --optimize-autoloader --no-dev

# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize

# Build frontend assets
npm run build
```

### 3. Files to Exclude from Upload

DO NOT upload these to production:
- `.env.example`
- `.git/` folder
- `node_modules/` folder
- `tests/` folder
- `*.md` documentation files (except README if needed)
- `.editorconfig`, `.gitignore`, `.gitattributes`
- Development config files

## Deployment Steps for Shared Hosting

### Method 1: Manual FTP/SFTP Deployment (Quickest)

#### Step 1: Prepare Production Environment File
Create `.env.production` file locally with production settings:

```env
APP_NAME="Lab Manager"
APP_ENV=production
APP_KEY=base64:YOUR_SECURE_KEY_HERE
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=daily
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_production_db_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_strong_password

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email@domain.com
MAIL_PASSWORD=your_mail_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

#### Step 2: Prepare Files Locally

1. **Clear all caches:**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```

2. **Install production dependencies:**
   ```bash
   composer install --optimize-autoloader --no-dev
   ```

3. **Build assets:**
   ```bash
   npm run build
   ```

4. **Create deployment package:**
   - Copy entire project to a staging folder
   - Remove files listed in "Files to Exclude" section
   - Keep only `vendor/` (with production dependencies)
   - Keep `public/build/` (compiled assets)

#### Step 3: Upload to Shared Hosting

**Standard Shared Hosting Structure:**
```
/home/username/
├── public_html/              # Your web root
└── laravel_app/              # Private folder (outside web root)
```

**Upload locations:**

1. **Upload Laravel application (EXCEPT public folder):**
   - Upload to: `/home/username/laravel_app/`
   - Include: `app/`, `bootstrap/`, `config/`, `database/`, `resources/`, `routes/`, `storage/`, `vendor/`, `artisan`, `composer.json`, etc.
   - **DO NOT** upload the `public/` folder here

2. **Upload public folder contents:**
   - Upload contents of `public/` folder to: `/home/username/public_html/`
   - This includes: `index.php`, `.htaccess`, `build/`, `storage/`, `robots.txt`, etc.

3. **Upload .env file:**
   - Rename `.env.production` to `.env`
   - Upload to: `/home/username/laravel_app/.env`
   - Set permissions: 600

#### Step 4: Modify index.php

Edit `/home/username/public_html/index.php`:

**Find these lines:**
```php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
```

**Replace with:**
```php
require __DIR__.'/../laravel_app/vendor/autoload.php';
$app = require_once __DIR__.'/../laravel_app/bootstrap/app.php';
```

#### Step 5: Update .htaccess (if needed)

In `/home/username/public_html/.htaccess`, ensure it has:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Disable directory browsing
Options -Indexes

# Prevent access to .env and other sensitive files
<Files .env>
    Order allow,deny
    Deny from all
</Files>

<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>
```

#### Step 6: Set File Permissions via FTP/SSH

```bash
# If you have SSH access:
cd /home/username/laravel_app
chmod -R 755 storage bootstrap/cache
chmod 600 .env

# If using FTP, set:
# Folders: 755
# Files: 644
# storage/ and bootstrap/cache/ folders: 775 (recursive)
# .env file: 600
```

#### Step 7: Run Database Migrations

If you have SSH access:
```bash
cd /home/username/laravel_app
php artisan migrate --force
```

If NO SSH access:
- Create a temporary migration route in `routes/web.php`:
```php
Route::get('/run-migrations-secret-key-12345', function() {
    if (app()->environment('production')) {
        Artisan::call('migrate', ['--force' => true]);
        return 'Migrations completed!';
    }
    return 'Not allowed';
});
```
- Visit: `https://yourdomain.com/run-migrations-secret-key-12345`
- **IMMEDIATELY remove this route after use**

#### Step 8: Create Symbolic Link for Storage

If SSH access:
```bash
cd /home/username/laravel_app
php artisan storage:link
```

If NO SSH access, create route:
```php
Route::get('/setup-storage-link-secret-12345', function() {
    Artisan::call('storage:link');
    return 'Storage link created!';
});
```
Visit the URL, then remove the route.

#### Step 9: Cache for Production

If SSH access:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Method 2: Git-Based Deployment (For Git-Enabled Hosting)

If your hosting supports Git:

1. **Push to repository:**
   ```bash
   git add .
   git commit -m "Production ready"
   git push origin main
   ```

2. **On server (via SSH):**
   ```bash
   cd /home/username/laravel_app
   git clone https://github.com/yourusername/labmanager.git .
   composer install --optimize-autoloader --no-dev
   cp .env.example .env
   nano .env  # Edit with production settings
   php artisan key:generate
   php artisan migrate --force
   php artisan storage:link
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

## Post-Deployment Verification

### Test these features:
1. [ ] Homepage loads correctly
2. [ ] Login/Authentication works
3. [ ] Database connections work
4. [ ] File uploads work
5. [ ] Email sending works (if configured)
6. [ ] All Nova admin features work
7. [ ] PDF generation works (dompdf)
8. [ ] Excel exports work
9. [ ] No errors in logs (`storage/logs/`)

### Monitor logs:
```bash
tail -f /home/username/laravel_app/storage/logs/laravel.log
```

## Security Best Practices

### 1. Hide Laravel Version
Remove `X-Powered-By` header in `.htaccess`:
```apache
Header unset X-Powered-By
```

### 2. Enable HTTPS
- Get free SSL from Let's Encrypt (usually available in cPanel)
- Force HTTPS in `.htaccess` (add at top):
```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### 3. Disable Directory Listing
Already in .htaccess: `Options -Indexes`

### 4. Protect Sensitive Files
```apache
<FilesMatch "(^\.env|^composer\.json|^composer\.lock|^package\.json)">
    Order allow,deny
    Deny from all
</FilesMatch>
```

### 5. Rate Limiting
Already configured in Laravel - verify in `app/Http/Kernel.php`

### 6. Regular Backups
- Database: Daily automated backups
- Files: Weekly backups
- Store backups off-site

### 7. Update Dependencies Regularly
```bash
composer update
npm update
```

## Troubleshooting Common Issues

### 500 Internal Server Error
- Check file permissions (storage/ must be writable)
- Check `.env` file exists and has correct settings
- Check error logs: `storage/logs/laravel.log`
- Verify PHP version (minimum 8.1)

### CSS/JS Not Loading
- Check `APP_URL` in `.env` matches your domain
- Verify `public/build/` folder exists
- Run `npm run build` and re-upload

### Database Connection Error
- Verify database credentials in `.env`
- Check database server hostname (usually `localhost`)
- Ensure database user has proper privileges

### File Upload Errors
- Check `storage/app/` permissions (775)
- Verify `storage/` is writable
- Check PHP `upload_max_filesize` and `post_max_size`

### Laravel Storage Symbolic Link Issues
- Some shared hosting doesn't allow symlinks
- Alternative: Copy `storage/app/public/` to `public/storage/`
- Or use full paths in code instead of storage URLs

## Quick Deployment Script

Create `deploy.sh` for future updates:

```bash
#!/bin/bash
echo "Starting deployment..."

# Put application in maintenance mode
php artisan down

# Pull latest changes (if using Git)
# git pull origin main

# Install/update dependencies
composer install --optimize-autoloader --no-dev

# Clear old caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Run migrations
php artisan migrate --force

# Cache everything
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Build assets
npm run build

# Bring application back up
php artisan up

echo "Deployment complete!"
```

## Maintenance Mode

When performing updates:
```bash
# Enable maintenance mode
php artisan down --message="Scheduled maintenance in progress" --retry=60

# Perform updates...

# Disable maintenance mode
php artisan up
```

## Performance Tips for Shared Hosting

1. **Use OPcache** - Usually enabled, verify in `phpinfo()`
2. **Enable Gzip compression** - Add to `.htaccess`:
   ```apache
   <IfModule mod_deflate.c>
       AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
   </IfModule>
   ```
3. **Browser caching** - Add to `.htaccess`:
   ```apache
   <IfModule mod_expires.c>
       ExpiresActive On
       ExpiresByType image/jpg "access plus 1 year"
       ExpiresByType image/jpeg "access plus 1 year"
       ExpiresByType image/gif "access plus 1 year"
       ExpiresByType image/png "access plus 1 year"
       ExpiresByType text/css "access plus 1 month"
       ExpiresByType application/javascript "access plus 1 month"
   </IfModule>
   ```
4. **Optimize images** before uploading
5. **Use CDN** for static assets if available

## Contact & Support

For deployment issues:
- Check Laravel logs: `storage/logs/laravel.log`
- Check PHP error logs (location varies by host)
- Contact hosting support for server-specific issues
- Review Laravel documentation: https://laravel.com/docs/10.x/deployment

---

**Remember:** Always backup your database before deployment and test in a staging environment if possible!
