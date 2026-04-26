#!/usr/bin/env sh
set -eu

mkdir -p /var/run/php /run/nginx
chown -R nginx:nginx /var/run/php /run/nginx /var/log/nginx

# Render Nginx config from template with runtime-configurable internal port.
envsubst '${WEB_INTERNAL_PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/http.d/default.conf

php-fpm -D
exec nginx -g 'daemon off;'
