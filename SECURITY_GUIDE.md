# Production Security Hardening Guide

## Critical Security Settings

### 1. Environment Configuration (.env)

**Required Settings:**
```env
APP_ENV=production
APP_DEBUG=false              # CRITICAL - Never true in production!
APP_URL=https://yourdomain.com

LOG_LEVEL=error              # Don't log everything
SESSION_SECURE_COOKIE=true   # Force HTTPS for cookies
SESSION_SAME_SITE=lax        # CSRF protection
```

### 2. File Permissions (Critical!)

```bash
# Application files
find /path/to/laravel -type f -exec chmod 644 {} \;
find /path/to/laravel -type d -exec chmod 755 {} \;

# Writable directories
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/

# Environment file (most important!)
chmod 600 .env

# Artisan script
chmod 755 artisan
```

### 3. .htaccess Security Headers

Ensure your `public/.htaccess` includes:

```apache
# Prevent access to sensitive files
<FilesMatch "(^\.env|^\.git|composer\.(json|lock)|package(-lock)?\.json|\.md$)">
    Order allow,deny
    Deny from all
</FilesMatch>

# Security Headers
<IfModule mod_headers.c>
    # Prevent clickjacking
    Header always set X-Frame-Options "SAMEORIGIN"
    
    # Prevent MIME sniffing
    Header always set X-Content-Type-Options "nosniff"
    
    # XSS Protection
    Header always set X-XSS-Protection "1; mode=block"
    
    # Referrer Policy
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    
    # Hide server info
    Header unset X-Powered-By
    Header always unset X-Powered-By
    ServerSignature Off
</IfModule>

# Disable directory browsing
Options -Indexes -ExecCGI

# Force HTTPS (after SSL is installed)
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### 4. Database Security

**Create restricted database user:**
```sql
-- Don't use root in production!
CREATE USER 'labmanager_user'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD_HERE';

-- Grant only necessary privileges
GRANT SELECT, INSERT, UPDATE, DELETE ON labmanager_db.* TO 'labmanager_user'@'localhost';

-- DO NOT grant: DROP, ALTER, CREATE, INDEX, REFERENCES
FLUSH PRIVILEGES;
```

**Strong password requirements:**
- Minimum 16 characters
- Mix of uppercase, lowercase, numbers, symbols
- Not related to domain or application name

### 5. Laravel Security Configuration

#### config/app.php
```php
'debug' => env('APP_DEBUG', false), // Default to false
'url' => env('APP_URL', 'https://yourdomain.com'),
```

#### config/session.php
```php
'secure' => env('SESSION_SECURE_COOKIE', true), // Only HTTPS
'same_site' => env('SESSION_SAME_SITE', 'lax'), // CSRF protection
'http_only' => true, // Prevent XSS cookie theft
```

#### config/database.php
```php
// Use prepared statements (default in Laravel)
// Never concatenate SQL queries
```

### 6. Rate Limiting

Already configured in Laravel, but verify in `app/Http/Kernel.php`:

```php
protected $middlewareGroups = [
    'web' => [
        // ...
        \Illuminate\Routing\Middleware\ThrottleRequests::class.':web',
    ],
];
```

Add custom rate limits if needed:
```php
// In routes/web.php or routes/api.php
Route::middleware('throttle:60,1')->group(function () {
    // Your routes here - 60 requests per minute
});
```

### 7. CSRF Protection

**Verify CSRF is enabled (default in Laravel):**
- All forms must include: `@csrf`
- API routes should use Sanctum or similar

### 8. SQL Injection Prevention

**Always use:**
```php
// ✅ GOOD - Parameterized queries
DB::table('users')->where('email', $email)->first();

// ✅ GOOD - Eloquent ORM
User::where('email', $email)->first();

// ❌ BAD - Raw SQL concatenation
DB::select("SELECT * FROM users WHERE email = '$email'");
```

### 9. XSS Prevention

**In Blade templates:**
```php
// ✅ GOOD - Escaped output
{{ $variable }}

// ⚠️ CAUTION - Unescaped (only for trusted data)
{!! $trustedHtml !!}

// ✅ GOOD - JavaScript
<script>
    var data = @json($data); // Properly escaped
</script>
```

### 10. File Upload Security

**If you have file uploads:**

```php
// In validation
$request->validate([
    'file' => 'required|file|mimes:pdf,jpg,png|max:10240', // 10MB
]);

// Store securely
$path = $request->file('file')->store('uploads', 'private');

// Never trust user-provided filenames
$extension = $file->getClientOriginalExtension();
$filename = Str::random(40) . '.' . $extension;
```

**In .htaccess for uploads directory:**
```apache
# Prevent PHP execution in upload directories
<FilesMatch "\.php$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

### 11. Nova Admin Panel Security

**Protect Nova routes:**

```php
// In config/nova.php
'path' => env('NOVA_PATH', 'admin'), // Change from default 'nova'

// In app/Providers/NovaServiceProvider.php
protected function gate()
{
    Gate::define('viewNova', function ($user) {
        return in_array($user->email, [
            // Only specific emails
            'admin@yourdomain.com',
        ]);
    });
}
```

**Add IP restriction (optional):**
```apache
# In public/.htaccess
<Location /admin>
    Order Deny,Allow
    Deny from all
    Allow from YOUR.IP.ADDRESS.HERE
</Location>
```

### 12. Error Handling

**Never expose stack traces in production:**

```php
// config/app.php
'debug' => false,

// app/Exceptions/Handler.php
public function render($request, Throwable $exception)
{
    if ($this->shouldReport($exception) && app()->environment('production')) {
        // Log the real error
        Log::error($exception->getMessage());
        
        // Show generic message to user
        return response()->view('errors.500', [], 500);
    }
    
    return parent::render($request, $exception);
}
```

### 13. Logging Security

```php
// config/logging.php
'channels' => [
    'daily' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
        'level' => env('LOG_LEVEL', 'error'),
        'days' => 14, // Rotate logs
        'permission' => 0644,
    ],
],
```

**Protect logs from web access:**
```apache
# In public/.htaccess
<DirectoryMatch "storage/logs">
    Order allow,deny
    Deny from all
</DirectoryMatch>
```

### 14. SSL/TLS Configuration

**Minimum requirements:**
- Valid SSL certificate (Let's Encrypt is free)
- TLS 1.2 or higher
- Force HTTPS redirect
- HSTS header (add to .htaccess):

```apache
<IfModule mod_headers.c>
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
</IfModule>
```

### 15. Backup Strategy

**Critical - Set up automated backups:**

1. **Database backups:**
   - Daily automated dumps
   - Store off-server (S3, Dropbox, etc.)
   - Test restoration monthly

2. **File backups:**
   - Weekly full backups
   - Daily incremental backups
   - Include: uploads, storage/app/

**Backup script example:**
```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u user -p'password' database > backup_$DATE.sql
tar -czf files_$DATE.tar.gz /path/to/storage/app/
# Upload to remote storage
```

### 16. Security Monitoring

**Log important events:**
```php
// In your controllers
Log::info('User login', ['user_id' => $user->id, 'ip' => $request->ip()]);
Log::warning('Failed login attempt', ['email' => $request->email]);
Log::error('Unauthorized access attempt', ['route' => $request->path()]);
```

**Monitor for:**
- Failed login attempts
- Unauthorized access attempts
- File upload activities
- Database connection errors
- Unusual traffic patterns

### 17. Regular Maintenance

**Weekly:**
- [ ] Review error logs
- [ ] Check for failed login attempts
- [ ] Monitor disk space

**Monthly:**
- [ ] Update Laravel: `composer update`
- [ ] Update npm packages: `npm update`
- [ ] Review user permissions
- [ ] Test backup restoration
- [ ] Security audit

**Quarterly:**
- [ ] Review and update SSL certificate
- [ ] Penetration testing (if budget allows)
- [ ] Code security review

### 18. Additional Security Measures

**Disable unused features:**
```php
// In config/app.php
// Remove providers you don't use

// Disable registration if not needed
// In routes/web.php
Auth::routes(['register' => false]);
```

**Content Security Policy (advanced):**
```apache
<IfModule mod_headers.c>
    Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline';"
</IfModule>
```

### 19. Third-Party Package Security

**Before using any package:**
- Check GitHub stars and recent activity
- Review open issues
- Check for known vulnerabilities: https://github.com/FriendsOfPHP/security-advisories
- Keep packages updated

**Run security audit:**
```bash
composer audit
npm audit
```

### 20. Security Checklist

- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] Strong `APP_KEY` generated
- [ ] `.env` file permissions = 600
- [ ] `storage/` permissions = 775
- [ ] HTTPS enabled and forced
- [ ] Security headers in .htaccess
- [ ] Strong database password
- [ ] Database user has minimal privileges
- [ ] CSRF protection enabled
- [ ] Rate limiting configured
- [ ] File upload validation
- [ ] Error pages don't reveal info
- [ ] Nova admin path changed
- [ ] Logs rotated and protected
- [ ] Backups automated
- [ ] SSL certificate valid
- [ ] Dependencies updated
- [ ] Security monitoring enabled

---

## Emergency Response Plan

**If you suspect a security breach:**

1. **Immediate actions:**
   ```bash
   # Enable maintenance mode
   php artisan down
   
   # Change all passwords (database, admin users, .env secrets)
   # Revoke API tokens
   # Check logs for suspicious activity
   ```

2. **Investigation:**
   - Check `storage/logs/` for unauthorized access
   - Review database for unexpected changes
   - Check file modifications: `find . -mtime -1 -type f`

3. **Recovery:**
   - Restore from clean backup if needed
   - Patch security vulnerability
   - Update all dependencies
   - Run security audit

4. **Prevention:**
   - Document what happened
   - Implement additional security measures
   - Set up better monitoring

---

## Resources

- **Laravel Security Best Practices:** https://laravel.com/docs/10.x/security
- **OWASP Top 10:** https://owasp.org/www-project-top-ten/
- **Security Headers Test:** https://securityheaders.com/
- **SSL Test:** https://www.ssllabs.com/ssltest/

---

**Remember: Security is an ongoing process, not a one-time setup!**
