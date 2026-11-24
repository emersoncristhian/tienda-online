FROM wordpress:php8.1-apache

# Ajustes de PHP optimizados para WooCommerce e importaciones
RUN { \
    echo 'upload_max_filesize = 64M'; \
    echo 'post_max_size = 64M'; \
    echo 'memory_limit = 256M'; \
    echo 'max_execution_time = 300'; \
} > /usr/local/etc/php/conf.d/custom-wordpress.ini

# Copiar el código del proyecto al directorio web de Apache
COPY . /var/www/html/

# Limpiar archivos innecesarios dentro del servidor web
RUN rm -rf /var/www/html/init-db /var/www/html/Dockerfile* /var/www/html/render.yaml

# Asegurar que la carpeta uploads exista y tenga permisos iniciales
RUN mkdir -p /var/www/html/wp-content/uploads \
    && chown -R www-data:www-data /var/www/html

# Script de entrada para garantizar permisos en el disco persistente de Render
COPY docker-entrypoint-render.sh /usr/local/bin/
RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint-render.sh \
    && chmod +x /usr/local/bin/docker-entrypoint-render.sh

ENTRYPOINT ["docker-entrypoint-render.sh"]
CMD ["apache2-foreground"]
