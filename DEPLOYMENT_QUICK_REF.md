# Quick Deployment Reference

## 🚀 Quick Start (For Experienced Deployers)

### 1. Prepare Locally
```bash
# Windows
prepare-deployment.bat

# Linux/Mac
chmod +x prepare-deployment.sh
./prepare-deployment.sh
```

### 2. Server Structure
```
/home/username/
├── laravel_app/          # Laravel files here
└── public_html/          # Only public/ contents here
```

### 3. Edit public/index.php
Change paths from `..` to `../laravel_app`:
```php
require __DIR__.'/../laravel_app/vendor/autoload.php';
$app = require_once __DIR__.'/../laravel_app/bootstrap/app.php';
```

### 4. Set Permissions
```bash
chmod 600 laravel_app/.env
chmod -R 775 laravel_app/storage laravel_app/bootstrap/cache
```

### 5. Run Artisan Commands
```bash
cd laravel_app
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 6. Enable HTTPS
Uncomment in `.htaccess`:
```apache
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## 📦 Files to Upload

### ✅ Upload These:
- `app/`
- `bootstrap/`
- `config/`
- `database/` (migrations and seeders)
- `public/` (to public_html)
- `resources/`
- `routes/`
- `storage/` (with 775 permissions)
- `vendor/` (production dependencies)
- `.env` (production version, 600 permissions)
- `.htaccess` (production version)
- `artisan`
- `composer.json`
- `composer.lock`

### ❌ Do NOT Upload:
- `node_modules/`
- `.git/`
- `tests/`
- `.env.example`
- `*.md` files
- `.editorconfig`
- `.gitignore`
- `.gitattributes`

---

## 🔐 Security Checklist

- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] `.env` permissions = 600
- [ ] HTTPS enabled
- [ ] Strong database password
- [ ] `.htaccess` security headers enabled
- [ ] No debug info visible on errors

---

## 🐛 Troubleshooting

| Problem | Solution |
|---------|----------|
| 500 Error | Check `storage/` permissions (775) |
| White Screen | Check `storage/logs/laravel.log` |
| CSS/JS Missing | Verify `APP_URL` in `.env` |
| Database Error | Check credentials in `.env` |
| Routes not working | Check `.htaccess` and mod_rewrite |

---

## 📝 .env Production Template

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
DB_HOST=localhost
DB_DATABASE=your_db_name
DB_USERNAME=your_db_user
DB_PASSWORD=strong_password_here
```

---

## 🔄 For Shared Hosting WITHOUT SSH

Use temporary deployment routes (see `deployment-routes-temp.php`):

1. Add routes from `deployment-routes-temp.php` to `routes/web.php`
2. Change the `$deploymentSecret` to something random
3. Visit: `https://yourdomain.com/deploy-migrate-YOUR_SECRET`
4. Visit: `https://yourdomain.com/deploy-storage-link-YOUR_SECRET`
5. Visit: `https://yourdomain.com/deploy-cache-config-YOUR_SECRET`
6. **IMMEDIATELY remove those routes from web.php**

---

## 📞 Support

- Laravel Docs: https://laravel.com/docs/10.x/deployment
- Logs Location: `storage/logs/laravel.log`

---

**Full Guide:** See `DEPLOYMENT_GUIDE.md`
**Checklist:** See `DEPLOYMENT_CHECKLIST.md`
