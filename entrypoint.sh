#!/bin/sh
set -e

# 1. Remove any stale Vite hot reload file in production
rm -f /var/www/html/public/hot

# 2. Database connection auto-detection & fallback:
# If DB_DATABASE points to a .sqlite or .db file, force DB_CONNECTION to sqlite
case "$DB_DATABASE" in
    *.sqlite*|*.db*)
        echo "[Entrypoint] Detected SQLite path in DB_DATABASE ($DB_DATABASE). Setting DB_CONNECTION=sqlite."
        export DB_CONNECTION=sqlite
        ;;
esac

# If DB_CONNECTION is pgsql or mysql, but no remote host/DATABASE_URL is provided,
# there is no local database server in this container, so fallback to sqlite.
if [ -z "$DATABASE_URL" ] && [ -z "$DB_URL" ]; then
    if [ "$DB_CONNECTION" = "pgsql" ] || [ "$DB_CONNECTION" = "mysql" ]; then
        if [ -z "$DB_HOST" ] || [ "$DB_HOST" = "127.0.0.1" ] || [ "$DB_HOST" = "localhost" ]; then
            echo "[Entrypoint] Warning: DB_CONNECTION is set to '$DB_CONNECTION' on 127.0.0.1, but no local database server is running."
            echo "[Entrypoint] Falling back to SQLite automatically."
            export DB_CONNECTION=sqlite
            export DB_DATABASE="${DB_DATABASE:-/var/lib/data/database.sqlite}"
        fi
    fi
fi

# Ensure default to sqlite if unset
export DB_CONNECTION="${DB_CONNECTION:-sqlite}"

# 3. Setup SQLite database file & directory if SQLite is active
if [ "$DB_CONNECTION" = "sqlite" ]; then
    DB_PATH="${DB_DATABASE:-/var/lib/data/database.sqlite}"
    export DB_DATABASE="$DB_PATH"
    DB_DIR=$(dirname "$DB_PATH")
    mkdir -p "$DB_DIR"
    if [ ! -f "$DB_PATH" ]; then
        touch "$DB_PATH"
    fi
    chown -R www-data:www-data "$DB_DIR"
    chmod -R 775 "$DB_DIR"
fi

# 4. If DATABASE_URL is set (e.g. from Render PostgreSQL), map to DB_URL
if [ -n "$DATABASE_URL" ] && [ -z "$DB_URL" ]; then
    export DB_URL="$DATABASE_URL"
fi

# 5. Ensure framework storage and cache directories exist with correct permissions
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 6. Bind to Render dynamic $PORT if provided
if [ -n "$PORT" ] && [ "$PORT" != "80" ]; then
    sed -ri -e "s!Listen 80!Listen ${PORT}!g" /etc/apache2/ports.conf
    sed -ri -e "s!<VirtualHost \*:80>!<VirtualHost \*:${PORT}>!g" /etc/apache2/sites-available/000-default.conf
fi

# 7. Run database migrations
php artisan migrate --force

# 8. Seed essential database records if needed
php artisan db:seed --force || true

# 9. Start Apache foreground
exec apache2-foreground
