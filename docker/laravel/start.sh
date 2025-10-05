#!/bin/bash

# Ajustar permissões
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Iniciar Supervisor em background
/usr/bin/supervisord -c /etc/supervisor/supervisord.conf &

# Iniciar PHP-FPM em foreground
php-fpm
