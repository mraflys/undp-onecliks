# Laravel 12 Upgrade Summary

## Tanggal Upgrade

31 Desember 2025

## Versi

-   **Dari:** Laravel 11.47.0
-   **Ke:** Laravel 12.44.0

## Perubahan Dependency

### composer.json

Dependency yang diupdate:

**Framework & Tools:**

-   `laravel/framework`: `^11.0` → `^12.0`
-   `laravel/tinker`: `^2.9` → `^2.10`
-   `yajra/laravel-datatables-oracle`: `^11.0` → `^12.0`

**Development Dependencies:**

-   `phpunit/phpunit`: `^10.5` → `^11.0`
-   `nunomaduro/collision`: `^8.0` → `^8.8.3` (compatible with Laravel 12)

## Perubahan File Konfigurasi

### 1. phpunit.xml

**Perubahan:** Update ke PHPUnit 11 schema dan konfigurasi baru

-   Update schema ke PHPUnit 11.0
-   Ganti `<filter><whitelist>` dengan `<source><include>`
-   Tambahkan konfigurasi `<coverage>` baru
-   Update `MAIL_DRIVER` menjadi `MAIL_MAILER`
-   Tambahkan konfigurasi database testing (SQLite in-memory)

### 2. config/mail.php

**Perubahan:** Update konfigurasi mail driver

-   `'driver' => env('MAIL_DRIVER', 'smtp')` → `'default' => env('MAIL_MAILER', 'smtp')`
-   Update dokumentasi untuk mencerminkan perubahan dari "driver" ke "mailer"

### 3. app/Http/Middleware/TrustProxies.php

**Perubahan:** Format indentasi untuk property `$headers`

-   Perbaiki format multi-line untuk konstanta headers

### 4. app/Exceptions/Handler.php

**Perubahan:** Update exception handling

-   Ganti `$this->isHttpException($exception)` dengan `$exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException`
-   Ini mengatasi issue dengan method `getStatusCode()` yang undefined

## File yang Dihapus

### app/Http/Middleware/CheckForMaintenanceMode.php

**Alasan:** Middleware ini sudah tidak diperlukan di Laravel 11+ karena maintenance mode sudah ditangani secara otomatis oleh framework.

## Perubahan Penting Lainnya

### Mail Configuration

Laravel 12 menggunakan `MAIL_MAILER` sebagai pengganti `MAIL_DRIVER`. Pastikan untuk update environment variables:

```
# Lama
MAIL_DRIVER=smtp

# Baru
MAIL_MAILER=smtp
```

### PHPUnit 11

PHPUnit 11 membawa beberapa perubahan breaking:

1. Update schema XML
2. Perubahan struktur coverage reporting
3. Beberapa assertion methods mungkin deprecated

### Bootstrap Architecture

Laravel 11+ menggunakan `bootstrap/app.php` untuk konfigurasi aplikasi, menggantikan `app/Http/Kernel.php` untuk routing middleware. File `app/Http/Kernel.php` masih ada untuk backward compatibility tapi tidak digunakan.

## Testing

Setelah upgrade, pastikan untuk:

1. **Clear cache:**

    ```bash
    php artisan config:clear
    php artisan cache:clear
    php artisan view:clear
    php artisan route:clear
    ```

2. **Update environment variables:**

    - Update `.env` file dengan `MAIL_MAILER` jika masih menggunakan `MAIL_DRIVER`

3. **Run tests:**

    ```bash
    php artisan test
    # atau
    vendor/bin/phpunit
    ```

4. **Check application:**
    - Jalankan aplikasi dan test semua fitur utama
    - Perhatikan terutama mail sending, authentication, dan middleware

## Catatan Tambahan

### Compatibility

Semua package pihak ketiga sudah diupdate ke versi yang kompatibel dengan Laravel 12:

-   ✅ barryvdh/laravel-dompdf: ^3.0
-   ✅ maatwebsite/excel: ^3.1
-   ✅ yajra/laravel-datatables-oracle: ^12.0

### Breaking Changes

Tidak ada breaking changes yang signifikan yang mempengaruhi kode aplikasi existing, kecuali:

1. Perubahan exception handling di Handler.php
2. Penghapusan CheckForMaintenanceMode middleware

### Security

Update ini juga membawa security patches terbaru dari Laravel 12. Pastikan untuk menjalankan:

```bash
composer audit
```

## Kesimpulan

Upgrade ke Laravel 12 berhasil dilakukan dengan perubahan minimal pada kode aplikasi. Aplikasi seharusnya berfungsi normal setelah upgrade. Monitoring aplikasi di production environment disarankan untuk beberapa hari pertama setelah deployment.

## Referensi

-   [Laravel 12 Upgrade Guide](https://laravel.com/docs/12.x/upgrade)
-   [PHPUnit 11 Documentation](https://docs.phpunit.de/en/11.0/)
