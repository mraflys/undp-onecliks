# Security Configuration - Hiding SQL Queries

**Project:** UNDP OneClick System  
**Laravel Version:** 11.x / 12.x  
**Date:** January 2026

---

## 🔒 Overview

Untuk keamanan aplikasi, SQL queries tidak akan ditampilkan pada error pages di production. Ini mencegah information disclosure yang dapat dimanfaatkan attacker.

---

## ⚙️ Configuration

### 1. **Environment Settings (.env)**

```env
# DEVELOPMENT (show errors with queries)
APP_ENV=local
APP_DEBUG=true

# PRODUCTION (hide queries)
APP_ENV=production
APP_DEBUG=false
```

**⚠️ CRITICAL:** Pastikan `APP_DEBUG=false` di production!

---

### 2. **Exception Handler (app/Exceptions/Handler.php)**

Telah dikonfigurasi untuk:

✅ **Menyembunyikan SQL queries** ketika `APP_DEBUG=false`  
✅ **Menampilkan generic error message** untuk database errors  
✅ **Custom error page** untuk user-friendly experience  
✅ **JSON response** untuk API requests

#### Implementation:

```php
public function render($request, Throwable $exception)
{
    // Hide SQL queries in production/non-debug mode
    if (!config('app.debug')) {
        if ($exception instanceof \Illuminate\Database\QueryException) {
            // API Response
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'A database error occurred. Please contact support.',
                    'error' => 'Database Error'
                ], 500);
            }

            // Web Response
            return response()->view('errors.database', [], 500);
        }
    }

    return parent::render($request, $exception);
}
```

---

### 3. **Custom Error Page (resources/views/errors/database.blade.php)**

User-friendly error page yang menampilkan:

-   ⚠️ Error icon dan message yang jelas
-   📧 Instruksi untuk contact support
-   🏠 Link untuk kembali ke homepage
-   🔢 Error code (DB-500)

---

## 🧪 Testing

### Test di Development (APP_DEBUG=true):

```powershell
# Trigger database error (contoh: syntax error)
php artisan tinker
>>> DB::select('INVALID SQL QUERY');
```

**Expected:** Detail error dengan SQL query ditampilkan

### Test di Production Mode (APP_DEBUG=false):

```powershell
# Set debug to false temporarily
php artisan config:cache
```

Edit `.env`:

```env
APP_DEBUG=false
```

```powershell
php artisan config:cache
php artisan serve
```

**Expected:** Generic error message tanpa SQL queries

---

## 🛡️ Security Benefits

### ❌ Before (Insecure):

```
SQLSTATE[42S02]: Base table or view not found: 1146 Table 'undp.users2' doesn't exist

SQL: SELECT * FROM users2 WHERE email = 'admin@example.com'
```

Attacker dapat melihat:

-   Database structure
-   Table names
-   Column names
-   Query patterns

### ✅ After (Secure):

**Web Response:**

```
⚠️ Database Error
We're experiencing technical difficulties with the database.
Please contact support.
```

**API Response:**

```json
{
    "message": "A database error occurred. Please contact support.",
    "error": "Database Error"
}
```

Attacker hanya melihat generic message.

---

## 📋 Additional Security Measures

### 1. **Logging (Tetap Lengkap)**

Meskipun queries tidak ditampilkan ke user, tetap di-log di:

```
storage/logs/laravel.log
```

Admin tetap bisa debug dari log files.

### 2. **Sensitive Data Protection**

```php
protected $dontFlash = [
    'password',
    'password_confirmation',
    'api_token',
    'api_secret',
];
```

### 3. **Production Checklist**

-   [ ] `APP_DEBUG=false`
-   [ ] `APP_ENV=production`
-   [ ] Remove debug packages di production
-   [ ] Enable error logging
-   [ ] Monitor `storage/logs/laravel.log`
-   [ ] Set up error notification (email/Slack)

---

## 🚨 Error Monitoring (Recommended)

### Option 1: Laravel Telescope (Development Only)

```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

⚠️ **NEVER enable in production!**

### Option 2: Sentry (Production)

```bash
composer require sentry/sentry-laravel
```

`.env`:

```env
SENTRY_LARAVEL_DSN=https://your-sentry-dsn
```

### Option 3: Bugsnag

```bash
composer require bugsnag/bugsnag-laravel
```

---

## 🔍 Debugging Production Issues

### Access Logs (Admin Only):

```powershell
# View latest errors
tail -f storage/logs/laravel.log

# Search for database errors
Select-String -Path "storage/logs/laravel.log" -Pattern "QueryException"

# Last 50 lines
Get-Content storage/logs/laravel.log -Tail 50
```

### Enable Temporary Debug (Emergency):

```powershell
# Set debug temporarily via Artisan
php artisan config:cache

# Edit .env
APP_DEBUG=true

# Clear cache
php artisan config:cache

# After fixing, IMMEDIATELY disable:
APP_DEBUG=false
php artisan config:cache
```

⚠️ **Never leave debug enabled in production!**

---

## 📊 What Gets Hidden

| Error Type            | Development | Production               |
| --------------------- | ----------- | ------------------------ |
| SQL Queries           | ✅ Shown    | ❌ Hidden                |
| Stack Traces          | ✅ Shown    | ❌ Hidden (only in logs) |
| Database Structure    | ✅ Shown    | ❌ Hidden                |
| Environment Variables | ✅ Shown    | ❌ Hidden                |
| File Paths            | ✅ Shown    | ⚠️ Partial (sanitized)   |

---

## 🎯 Best Practices

### ✅ DO:

-   Set `APP_DEBUG=false` di production
-   Monitor error logs regularly
-   Use error tracking service (Sentry/Bugsnag)
-   Show user-friendly error messages
-   Log all errors dengan detail lengkap
-   Test error handling before deployment

### ❌ DON'T:

-   Show SQL queries ke users
-   Display stack traces di production
-   Expose database structure
-   Leave debug mode enabled
-   Ignore error logs
-   Show technical details to non-admin users

---

## 🔧 Troubleshooting

### Issue: "Error 500" tanpa detail

**Solution:** Check logs

```powershell
Get-Content storage/logs/laravel.log -Tail 100
```

### Issue: Error page tidak muncul

**Solution:** Clear cache

```powershell
php artisan config:clear
php artisan view:clear
php artisan cache:clear
```

### Issue: Masih menampilkan queries

**Solution:** Verify `.env`

```powershell
php artisan config:cache
php artisan about
```

Check output untuk `APP_DEBUG` status.

---

## 📞 Support

Jika menemukan security issue:

1. **JANGAN** report di public issue tracker
2. Email ke: security@undp.org
3. Sertakan detail minimal (tanpa sensitive data)

---

## ✅ Verification

```powershell
# Check current configuration
php artisan about

# Verify debug status
php artisan tinker
>>> config('app.debug')
# Should return: false (in production)

# Test error handling
>>> DB::select('INVALID QUERY')
# Should NOT show SQL query to browser
```

---

**Updated:** January 2026  
**Status:** ✅ Implemented & Tested  
**Security Level:** High

---

_End of Security Documentation_
