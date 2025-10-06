#!/bin/bash

# Ajustar permissões do Laravel para desenvolvimento
# UID/GID 33 = www-data no container
echo "Ajustando permissões do código e pastas de escrita..."
chown -R 33:33 /var/www
find /var/www -type d -exec chmod 755 {} \;
find /var/www -type f -exec chmod 644 {} \;
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Iniciar Supervisor (Horizon) em background
/usr/bin/supervisord -c /etc/supervisor/supervisord.conf &

# Iniciar PHP-FPM em foreground
php-fpm
