# Hostinger Shared Hosting Deployment Guide - Listing ERP

Follow these steps to deploy **Listing ERP** on Hostinger Shared Hosting without requiring a VPS, Docker, or Node server.

## Step 1: Prepare Database on Hostinger
1. Log in to your **Hostinger hPanel**.
2. Navigate to **Databases** > **MySQL Databases**.
3. Create a new database:
   - **Database Name**: e.g., `u123456789_listingerp`
   - **Username**: e.g., `u123456789_admin`
   - **Password**: Create a secure password.
4. Keep these database details handy.

## Step 2: Upload Files
1. Compress your project folder (excluding the `.git`, `node_modules` folders) into a `.zip` file.
2. In Hostinger hPanel, go to **Files** > **File Manager**.
3. Create a directory named `listing-core` outside the public root (at the same level as `public_html`).
4. Upload your `.zip` file to this `listing-core` folder and extract it.
5. Move the contents of the `public` folder inside `listing-core/public/*` directly into the `public_html` root directory.

## Step 3: Modify paths in `public_html/index.php`
Edit `public_html/index.php` in the Hostinger File Manager to reference the core directories correctly:

```php
// Register the Composer autoloader...
require __DIR__.'/../listing-core/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/../listing-core/bootstrap/app.php')
```

## Step 4: Setup Environment Configuration
1. Inside the `listing-core` folder, copy `.env.example` to `.env`.
2. Configure the database details in the `.env` file:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://yourdomain.com

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=u123456789_listingerp
   DB_USERNAME=u123456789_admin
   DB_PASSWORD=your_secure_password
   ```

## Step 5: Run Migrations and Dependencies
1. Open the Hostinger SSH Terminal (or use hPanel's Browser Console).
2. Run the dependency installation:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
3. Set your application key:
   ```bash
   php artisan key:generate
   ```
4. Access your website URL to initiate the setup wizard, or run the following command directly to populate parameters:
   ```bash
   php artisan migrate --force --seed
   ```
