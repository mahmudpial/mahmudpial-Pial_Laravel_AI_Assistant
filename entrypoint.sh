#!/bin/sh
set -e

# 1. Remove any stale Vite hot reload file in production
rm -f /var/www/html/public/hot

# 2. Setup SQLite database file & directory if SQLite is used
if [ "$DB_CONNECTION" = "sqlite" ] || [ -z "$DB_CONNECTION" ]; then
    DB_PATH=${DB_DATABASE:-/var/www/html/database/database.sqlite}
    DB_DIR=$(dirname "$DB_PATH")
    mkdir -p "$DB_DIR"
    touch "$DB_PATH"
    chown -R www-data:www-data "$DB_DIR"
    chmod -R 775 "$DB_DIR"
fi

# 3. Ensure framework storage and cache directories exist with correct permissions
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 4. Bind to Render dynamic $PORT if provided
if [ -n "$PORT" ] && [ "$PORT" != "80" ]; then
    sed -ri -e "s!Listen 80!Listen ${PORT}!g" /etc/apache2/ports.conf
    sed -ri -e "s!<VirtualHost \*:80>!<VirtualHost \*:${PORT}>!g" /etc/apache2/sites-available/000-default.conf
fi

# 5. Run database migrations
php artisan migrate --force

# 6. Seed essential database records if needed
php artisan db:seed --force || true

# 7. Start Apache foreground
exec apache2-foreground
