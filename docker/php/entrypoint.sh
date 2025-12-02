#!/bin/sh
set -e

# 1. Setup .env if missing
if [ ! -f .env ]; then
    echo "📝 .env not found. Creating from .env.example..."
    cp .env.example .env
else
    echo "✅ .env exists."
fi

echo "📦 Installing PHP dependencies..."
composer install --no-interaction --optimize-autoloader

# Ensure the application key exists before we touch the database
if ! grep -Eq "^APP_KEY=base64:" .env || grep -Eq "^APP_KEY=($|[[:space:]]*$)" .env; then
    echo "🔑 Generating application key..."
    php artisan key:generate --force
fi

# 2. Install Node dependencies (skip if already on disk unless FORCE_NPM_INSTALL is set)
if [ "${FORCE_NPM_INSTALL}" = "1" ] || [ ! -d "node_modules" ]; then
    echo "📦 Installing Node dependencies..."
    npm install --legacy-peer-deps
else
    echo "✅ Node dependencies already installed."
fi

# 3. Build frontend assets unless explicitly skipped
if [ "${SKIP_NPM_BUILD}" != "1" ]; then
    echo "🎨 Building frontend assets..."
    npm run build
else
    echo "⚠️  Skipping frontend build (SKIP_NPM_BUILD=1)."
fi

# 4. Run migrations unless opted out
if [ "${SKIP_MIGRATIONS}" != "1" ]; then
    echo "🗄️  Running migrations..."
    php artisan migrate --force
else
    echo "⚠️  Skipping migrations (SKIP_MIGRATIONS=1)."
fi

# Seed database unless explicitly skipped
if [ "${SKIP_DB_SEED}" != "1" ]; then
    echo "🌱 Seeding database..."
    php artisan db:seed --force
else
    echo "⚠️  Skipping database seeding (SKIP_DB_SEED=1)."
fi

# 5. Fix permissions
echo "🔒 Fixing permissions..."
chmod -R 777 storage bootstrap/cache 2>/dev/null || true

# 6. Execute the main command (php-fpm)
echo "🚀 Starting PHP-FPM..."
exec "$@"
