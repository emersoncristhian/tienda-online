#!/usr/bin/env bash
set -Eeuo pipefail

# Asegurar permisos de escritura para www-data en el disco persistente de uploads
if [ -d /var/www/html/wp-content/uploads ]; then
    chown -R www-data:www-data /var/www/html/wp-content/uploads
fi

# Delegar la ejecucion al entrypoint oficial de WordPress
exec docker-entrypoint.sh "$@"
