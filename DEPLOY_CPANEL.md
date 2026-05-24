# OmniShop — cPanel / Production Deployment

## Server layout

| Path | Purpose |
|------|---------|
| `/home2/omnispac/solar-and-storage-core/` | Application backend (private; not web-accessible) |
| `/home2/omnispac/public_html/shop/` | Public web root for `shop.omnispace3d.com` |
| `/home2/omnispac/public_html/shop/static/` | CSS, JS, product images (uploads preserved on deploy) |

`public/index.php` auto-detects production when `../../solar-and-storage-core/artisan` exists.

## First-time setup (SSH)

```bash
cd /home2/omnispac/solar-and-storage-core

# Create .env from template (or copy your prepared file)
cp .env.production.example .env
nano .env   # set APP_KEY, DB_PASS, confirm APP_URL

# Generate a unique APP_KEY (pick one method)
php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
# Paste result into APP_KEY= in .env

composer install --no-dev --optimize-autoloader --no-interaction

# Database tables
php artisan migrate --force

# Optional: seed admin user, settings, catalog stock (first deploy only)
# php artisan db:seed --force

chmod -R 775 storage bootstrap/cache
touch storage/logs/laravel.log
```

## cPanel Git deploy

1. cPanel → **Git Version Control** → clone `https://github.com/Juniortambo2628/shop-omnispace3d.git`
2. Set deployment target / enable **Automatic Deployment** if available.
3. Each push runs `.cpanel.yml`:
   - Syncs code → `solar-and-storage-core`
   - Runs `composer install`
   - Copies `static/` → `public_html/shop/static/` (keeps existing product uploads)
   - Copies `public/index.php` + `.htaccess` → `public_html/shop/`

After first deploy, ensure `.env` exists in `solar-and-storage-core` (not overwritten by deploy).

## Routine updates

```bash
# Via cPanel: Repository → Pull or Deploy HEAD
# Or SSH:
cd /home2/omnispac/repositories/shop-omnispace3d   # path may vary in cPanel
git pull origin main
# cPanel auto-runs .cpanel.yml, or trigger Deploy manually

cd /home2/omnispac/solar-and-storage-core
php artisan migrate --force
```

## Verify

- https://shop.omnispace3d.com/ → catalog redirect
- https://shop.omnispace3d.com/admin/login → admin
- https://shop.omnispace3d.com/up → health check

## Local `.env.production`

A full production `.env` with your credentials is in `.env.production` (gitignored). Copy to the server:

```bash
scp .env.production omnispac@shop.omnispace3d.com:/home2/omnispac/solar-and-storage-core/.env
```

Do **not** commit `.env` or `.env.production` to GitHub.
