# Production Deployment - Complete Package

This directory contains everything you need to deploy your Laravel Lab Manager application to a shared hosting environment securely and efficiently.

## 📚 Documentation Files

### Essential Guides (Read in Order)

1. **[DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md)** 📖 COMPREHENSIVE
   - Complete step-by-step instructions
   - Multiple deployment methods
   - Troubleshooting section
   - Performance optimization tips

2. **[SECURITY_GUIDE.md](SECURITY_GUIDE.md)** 🔐 CRITICAL
   - Security hardening checklist
   - File permissions
   - .htaccess configuration
   - Best practices

3. **[DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md)** ✅ REFERENCE
   - Before, during, and after deployment tasks
   - Testing checklist
   - Maintenance schedule

## 🛠️ Deployment Tools

### Scripts

- **`prepare-deployment.bat`** (Windows)
  - Run this before deployment on Windows
  - Clears caches, installs dependencies, builds assets
  
- **`prepare-deployment.sh`** (Linux/Mac)
  - Same as above but for Unix systems
  - Make executable: `chmod +x prepare-deployment.sh`

### Configuration Files

- **`.env.production.example`**
  - Template for production environment file
  - Copy this, fill in your details, rename to `.env`
  
- **`.htaccess.production`**
  - Enhanced .htaccess with security headers
  - Replace `public/.htaccess` with this file

- **`deployment-routes-temp.php`**
  - Temporary routes for servers without SSH
  - Only use if you can't run artisan commands via SSH
  - **REMOVE after use!**

## 🚀 Quick Start Guide

### For First-Time Deployment:

```bash
# 1. Prepare locally (Windows)
prepare-deployment.bat

# 2. Upload files via FTP:
#    - Laravel app files → /home/username/laravel_app/
#    - public/ contents → /home/username/public_html/

# 3. Edit public/index.php
#    Change "../vendor" to "../laravel_app/vendor"
#    Change "../bootstrap" to "../laravel_app/bootstrap"

# 4. Set permissions (via SSH or cPanel):
#    .env = 600
#    storage/ = 775 (recursive)
#    bootstrap/cache/ = 775 (recursive)

# 5. Run setup (if SSH available):
cd /home/username/laravel_app
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Test your site!
```

### Without SSH Access:

1. Follow steps 1-4 above
2. Add routes from `deployment-routes-temp.php` to `routes/web.php`
3. Visit the deployment URLs in your browser
4. **Immediately remove those routes!**

## 📋 Pre-Deployment Checklist

- [ ] Backup current database
- [ ] Configure `.env` for production
- [ ] Set `APP_DEBUG=false`
- [ ] Set `APP_ENV=production`
- [ ] Update `APP_URL` to production domain
- [ ] Run `prepare-deployment.bat` or `.sh`
- [ ] Test locally one final time

## 🔒 Security Priority List

### Critical (Do Before Going Live):
1. Set `APP_DEBUG=false`
2. Set `.env` permissions to 600
3. Enable HTTPS with valid SSL certificate
4. Use strong database password
5. Set storage/ permissions to 775

### Important (Do on First Day):
1. Change Nova admin path
2. Set up automated backups
3. Configure rate limiting
4. Review all file permissions
5. Test error pages

### Regular Maintenance:
1. Update dependencies monthly
2. Review logs weekly
3. Test backups monthly
4. Monitor security advisories

## 📁 File Upload Structure

```
Shared Hosting Server:
/home/username/
├── laravel_app/              ← Laravel application (private)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── resources/
│   ├── routes/
│   ├── storage/              ← Must be writable (775)
│   ├── vendor/
│   ├── .env                  ← Must be 600 permissions
│   └── artisan
└── public_html/              ← Web root (public access)
    ├── .htaccess             ← Use .htaccess.production
    ├── index.php             ← Must edit paths
    ├── robots.txt
    ├── build/                ← Compiled assets
    └── storage/              ← Symlink to storage/app/public
```

## 🚨 Common Issues & Solutions

| Issue | Solution |
|-------|----------|
| 500 Error | Check storage/ permissions (775) |
| White Screen | Check storage/logs/laravel.log |
| CSS/JS not loading | Verify APP_URL in .env |
| Database error | Check credentials in .env |
| Routes not working | Verify .htaccess exists |

## 📞 Getting Help

1. **Check Logs First:**
   - Laravel: `storage/logs/laravel.log`
   - PHP: Ask hosting for error log location
   
2. **Review Documentation:**
   - Start with DEPLOYMENT_QUICK_REF.md
   - Check SECURITY_GUIDE.md for security issues
   - Read full DEPLOYMENT_GUIDE.md for details

3. **Common Resources:**
   - Laravel Docs: https://laravel.com/docs/10.x/deployment
   - Security Headers: https://securityheaders.com/
   - SSL Test: https://www.ssllabs.com/ssltest/

## ⚡ What Makes This Different?

This deployment package is specifically designed for:
- ✅ Shared hosting environments (no VPS/Docker needed)
- ✅ Limited or no SSH access
- ✅ Quick deployment (15-30 minutes)
- ✅ Production-ready security
- ✅ Nova-enabled Laravel applications
- ✅ Beginner-friendly with expert options

## 📊 Deployment Time Estimate

- **With SSH:** 15-20 minutes
- **Without SSH:** 20-30 minutes
- **First time:** 30-45 minutes (reading docs + deployment)

## ✅ Post-Deployment Verification

After deployment, test these:
1. Homepage loads without errors
2. Login works
3. Nova admin accessible
4. Database queries work
5. File uploads work
6. PDF generation works
7. Excel exports work
8. HTTPS redirect works
9. No errors in browser console

## 🎯 Next Steps After Reading This

1. **Read:** [DEPLOYMENT_QUICK_REF.md](DEPLOYMENT_QUICK_REF.md) - 5 min
2. **Configure:** Copy `.env.production.example` → `.env` and fill details
3. **Prepare:** Run `prepare-deployment.bat` or `.sh`
4. **Deploy:** Follow the quick start guide above
5. **Secure:** Review [SECURITY_GUIDE.md](SECURITY_GUIDE.md)
6. **Verify:** Use [DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md)

---

## 📝 Notes

- All scripts are safe to run multiple times
- Always backup before deployment
- Test in staging if available
- Keep this documentation for future updates

## 🆘 Emergency Contacts

- **Hosting Support:** [Add your hosting support details]
- **Database Admin:** [Add if different]
- **Emergency Rollback:** See DEPLOYMENT_CHECKLIST.md

---

**Last Updated:** January 30, 2026
**Laravel Version:** 10.x
**PHP Version:** 8.1+
**Tested On:** Shared hosting (cPanel, Plesk compatible)

---

**Good luck with your deployment! 🚀**

If you've read this far, you're ready. The deployment is straightforward if you follow the guides. Start with DEPLOYMENT_QUICK_REF.md!
