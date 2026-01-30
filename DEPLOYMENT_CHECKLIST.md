# Deployment Checklist

## Before Deployment

### Local Preparation
- [ ] All code committed to Git
- [ ] Database backup created
- [ ] `.env.production.example` copied and configured as `.env` for production
- [ ] `APP_DEBUG=false` in production .env
- [ ] `APP_ENV=production` in production .env
- [ ] `APP_URL` updated to production domain
- [ ] Strong database password set
- [ ] Mail configuration tested and working
- [ ] Ran `prepare-deployment.bat` (Windows) or `prepare-deployment.sh` (Linux/Mac)

### Files Ready
- [ ] `composer install --no-dev --optimize-autoloader` completed
- [ ] `npm run build` completed successfully
- [ ] `public/build/` directory exists with compiled assets
- [ ] Removed development files (tests/, .git/, node_modules/)
- [ ] `.htaccess.production` ready to replace public/.htaccess

## During Deployment

### File Upload
- [ ] Uploaded Laravel files (app, bootstrap, config, database, resources, routes, storage, vendor) to `/home/username/laravel_app/`
- [ ] Uploaded public folder contents to `/home/username/public_html/`
- [ ] Uploaded `.env` file to `/home/username/laravel_app/.env`
- [ ] Replaced `public/.htaccess` with `.htaccess.production` version

### Configuration
- [ ] Modified `public/index.php` to point to correct Laravel directory
- [ ] Updated `public/.htaccess` with production version
- [ ] Set file permissions:
  - [ ] `.env` = 600
  - [ ] `storage/` = 775 (recursive)
  - [ ] `bootstrap/cache/` = 775 (recursive)
  - [ ] All other files = 644
  - [ ] All directories = 755

### Database
- [ ] Created production database
- [ ] Created database user with appropriate privileges
- [ ] Updated database credentials in `.env`
- [ ] Ran migrations: `php artisan migrate --force`
- [ ] (Optional) Seeded data if needed

### Laravel Setup
- [ ] Created storage link: `php artisan storage:link`
- [ ] Cached configuration: `php artisan config:cache`
- [ ] Cached routes: `php artisan route:cache`
- [ ] Cached views: `php artisan view:cache`

## After Deployment

### Testing
- [ ] Homepage loads without errors
- [ ] Login/authentication works
- [ ] Nova admin panel accessible
- [ ] Database queries work correctly
- [ ] File uploads work
- [ ] PDF generation works
- [ ] Excel exports work
- [ ] Email sending works (test with real email)
- [ ] All images and assets load correctly
- [ ] No JavaScript errors in browser console
- [ ] Mobile responsive design works

### Security Verification
- [ ] HTTPS enabled (SSL certificate installed)
- [ ] Force HTTPS redirect working
- [ ] `.env` file not accessible via browser
- [ ] `storage/` not accessible via browser
- [ ] Directory listing disabled
- [ ] Error pages don't reveal sensitive information
- [ ] Rate limiting working on login page

### Performance Check
- [ ] Page load time acceptable (< 3 seconds)
- [ ] Images optimized
- [ ] GZIP compression working
- [ ] Browser caching headers set
- [ ] OPcache enabled (check via phpinfo)

### Monitoring Setup
- [ ] Error logging working (`storage/logs/laravel.log`)
- [ ] Set up log rotation (if available)
- [ ] Set up automated backups (database + files)
- [ ] Set up uptime monitoring (optional)
- [ ] Documented admin credentials securely

## Troubleshooting Reference

### Common Issues

**500 Error**
- Check storage/ permissions (must be 775)
- Check .env file exists and is readable
- Check storage/logs/laravel.log for errors
- Verify PHP version >= 8.1

**404 on all pages except homepage**
- Check mod_rewrite is enabled
- Verify .htaccess file exists in public/
- Check .htaccess RewriteBase if in subdirectory

**CSS/JS not loading**
- Verify APP_URL in .env matches domain
- Check public/build/ directory exists
- Clear browser cache

**Database connection error**
- Verify database credentials in .env
- Check database server hostname
- Ensure database user has correct privileges

**Blank page / white screen**
- Enable error display temporarily (set display_errors=On in php.ini or .htaccess)
- Check PHP error logs
- Check storage/logs/laravel.log

### Emergency Rollback Plan
1. Enable maintenance mode: `php artisan down`
2. Restore previous version files
3. Restore database backup
4. Clear all caches
5. Disable maintenance mode: `php artisan up`

## Post-Deployment Maintenance

### Weekly
- [ ] Check error logs
- [ ] Monitor disk space
- [ ] Review security logs

### Monthly
- [ ] Update dependencies: `composer update` (test locally first)
- [ ] Review and optimize database
- [ ] Test backups restoration
- [ ] Check SSL certificate expiration

### As Needed
- [ ] Security patches
- [ ] Feature updates
- [ ] Performance optimization

## Support Contacts

- **Hosting Support:** [Your hosting support email/phone]
- **Laravel Documentation:** https://laravel.com/docs/10.x
- **Emergency Contact:** [Your email/phone]

---

**Date Deployed:** _______________
**Deployed By:** _______________
**Server:** _______________
**Notes:** _______________
