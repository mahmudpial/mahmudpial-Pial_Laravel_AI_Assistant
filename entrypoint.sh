#!/bin/sh
php artisan migrate --force
exec apache2-foreground
