# GitHub Actions Deployment — OmniShop

## Overview

This document covers the GitHub Actions CI/CD setup for automated deployment of OmniShop to the production server.

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    GitHub Repository                         │
│  ┌─────────────┐    ┌─────────────┐    ┌─────────────┐     │
│  │  Test        │───▶│  Deploy     │───▶│  Production │     │
│  │  Workflow    │    │  Workflow   │    │  Server     │     │
│  └─────────────┘    └─────────────┘    └─────────────┘     │
└─────────────────────────────────────────────────────────────┘
```

## Server Layout

| Path | Purpose |
|------|---------|
| `/home2/omnispac/solar-and-storage-core/` | Application backend (private) |
| `/home2/omnispac/public_html/shop/` | Public web root for shop.omnispace3d.com |
| `/home2/omnispac/public_html/shop/static/` | CSS, JS, images |

## Prerequisites

### 1. Generate SSH Key Pair

Run this on your local machine (Windows/Linux/Mac):

```bash
ssh-keygen -t ed25519 -C "github-actions-omnispace" -f github-actions-omnispace -N '""'
```

This creates:
- `github-actions-omnispace` — Private key (for GitHub Secrets)
- `github-actions-omnispace.pub` — Public key (for server)

### 2. Add Public Key to Server

SSH into your server and add the public key:

```bash
# SSH into server
ssh omnispac@162.241.30.16

# Add to authorized_keys
cat >> ~/.ssh/authorized_keys << 'EOF'
<contents of github-actions-omnispace.pub>
EOF

# Set correct permissions
chmod 600 ~/.ssh/authorized_keys
chmod 700 ~/.ssh
```

### 3. Add GitHub Secrets

Go to your GitHub repository → **Settings** → **Secrets and variables** → **Actions** → **New repository secret**

Add these secrets:

| Secret Name | Value | Description |
|-------------|-------|-------------|
| `SSH_PRIVATE_KEY` | Contents of `github-actions-omnispace` file | Private SSH key |
| `SSH_HOST` | `162.241.30.16` | Server IP address |
| `SSH_USER` | `omnispac` | SSH username |
| `SSH_PORT` | `22` | SSH port (optional, defaults to 22) |

**Important:** Copy the ENTIRE contents of the private key file, including the `-----BEGIN OPENSSH PRIVATE KEY-----` and `-----END OPENSSH PRIVATE KEY-----` lines.

## Deployment Process

### Automatic Deployment

1. Push to `main` branch
2. GitHub Actions automatically:
   - Runs all tests (PHPUnit, branding consistency, structure checks)
   - If tests pass, deploys to production

### Manual Deployment

1. Go to **Actions** tab in GitHub
2. Select **Deploy to Production** workflow
3. Click **Run workflow**

### What Gets Deployed

**Backend files** → `/home2/omnispac/solar-and-storage-core/`
- Application code (app/, core/, config/, routes/, views/, database/)
- composer.json and composer.lock
- artisan, config.php
- .env is **NEVER** overwritten

**Public files** → `/home2/omnispac/public_html/shop/`
- index.php, .htaccess
- All files from public/ directory

**Static files** → `/home2/omnispac/public_html/shop/static/`
- CSS, JS files
- Logo images
- Product images are **PRESERVED** (not overwritten)

### Post-Deploy Steps (Automatic)

The deployment workflow automatically:
1. ✅ Installs composer dependencies (`composer install --no-dev --optimize-autoloader`)
2. ✅ Runs database migrations (`php artisan migrate --force`)
3. ✅ Sets permissions (`chmod 775 storage bootstrap/cache`)
4. ✅ Verifies file structure
5. ✅ Clears and rebuilds caches
6. ✅ Cleans up old .env backups

## Testing

### Run Tests Locally

```bash
# Install dev dependencies
composer install

# Run all tests
vendor/bin/phpunit

# Run only unit tests
vendor/bin/phpunit --testsuite Unit

# Run only feature tests
vendor/bin/phpunit --testsuite Feature
```

### Test Coverage

The test suite includes:
- **Branding consistency tests** — Verify colors and typography match brand spec
- **CSS tokens tests** — Verify CSS variables match Branding class
- **Invoice tests** — Verify Invoice.php uses Branding class
- **Mailer tests** - Verify Mailer.php uses Branding class
- **Application structure tests** — Verify critical files and directories exist
- **Security tests** — Verify .env and secrets are gitignored

## Branding System

### Colors

All brand colors are defined in `core/Branding.php` (single source of truth):

| Color | Hex | Usage |
|-------|-----|-------|
| Brand Teal | `#0A9696` | PRIMARY — Headings, borders, logos |
| Light Teal | `#19AFAC` | ACCENT — Table headers, panels |
| Pale Teal | `#D6F0EF` | LIGHT FILL — Alternating rows, backgrounds |
| Charcoal | `#333333` | BODY TEXT — Main copy |
| Mid Grey | `#6E6E6E` | SUBHEADINGS — Captions, supporting text |
| White | `#FFFFFF` | BACKGROUND — Pages, text on teal |

### Typography

- **Headings:** Arial Bold
- **Body:** Arial Regular
- **Tagline:** Arial Italic — "Your Space, Your Way"

### Logos

| File | Usage |
|------|-------|
| `omnispace-logo.jpg` | Standard use on white/light backgrounds |
| `omnispace-logo-white.png` | For teal/dark backgrounds |

### CSS Variables

All colors are also defined as CSS custom properties in `static/css/tokens.css`:

```css
--brand-teal: #0A9696;
--brand-teal-accent: #19AFAC;
--brand-teal-pale: #D6F0EF;
--color-text: #333333;
--color-text-secondary: #6E6E6E;
```

## Troubleshooting

### Deployment Fails

1. **Check SSH key:** Ensure the private key is correctly added to GitHub Secrets
2. **Check server access:** Test SSH manually: `ssh -i github-actions-omnispace omnispac@162.241.30.16`
3. **Check permissions:** Ensure the public key is in `~/.ssh/authorized_keys` on the server

### Migrations Fail

```bash
# SSH into server
ssh omnispac@162.241.30.16

# Check migration status
cd /home2/omnispac/solar-and-storage-core
php artisan migrate:status

# Run pending migrations manually
php artisan migrate --force
```

### Permission Errors

```bash
# SSH into server
ssh omnispac@162.241.30.16

# Fix permissions
cd /home2/omnispac/solar-and-storage-core
chmod -R 775 storage bootstrap/cache
chown -R omnispac:omnispac storage bootstrap/cache
```

### .env Missing

The deployment does NOT create or overwrite `.env`. If it's missing:

```bash
# SSH into server
ssh omnispac@162.241.30.16

# Create .env
cd /home2/omnispac/solar-and-storage-core
cp .env.production.example .env

# Edit with your credentials
nano .env

# Generate APP_KEY
php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
# Paste the output into APP_KEY= in .env
```

## File Structure After Deployment

```
/home2/omnispac/
├── solar-and-storage-core/          # Backend (private)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── core/
│   │   ├── Branding.php            # Brand colors/fonts (single source)
│   │   ├── Invoice.php             # PDF invoice generator
│   │   └── Mailer.php              # Email sender
│   ├── data/
│   ├── database/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── views/
│   ├── .env                        # NOT deployed (must exist on server)
│   ├── artisan
│   ├── composer.json
│   └── config.php
│
└── public_html/shop/                # Public web root
    ├── index.php
    ├── .htaccess
    └── static/
        ├── css/
        │   ├── tokens.css          # Brand variables
        │   ├── base.css
        │   ├── components.css
        │   └── ...
        ├── js/
        └── images/
            ├── omnispace-logo.jpg
            ├── omnispace-logo-white.png
            └── products/           # User uploads (preserved)
```

## Security Notes

- `.env` files are **NEVER** committed to git or deployed
- SSH private keys are **NEVER** stored in the repository
- Product images are **NOT** overwritten during deployment
- Old `.env` backups are automatically cleaned (keeps last 5)
- All secrets are stored in GitHub Secrets (encrypted)
