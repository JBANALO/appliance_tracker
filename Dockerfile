FROM php:8.2-fpm

# Install nginx and required PHP extensions
RUN apt-get update && apt-get install -y \
    nginx \
    && docker-php-ext-install mysqli pdo pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Configure PHP-FPM to handle more concurrent requests
RUN echo '[www]\n\
pm = dynamic\n\
pm.max_children = 20\n\
pm.start_servers = 5\n\
pm.min_spare_servers = 2\n\
pm.max_spare_servers = 10\n\
pm.process_idle_timeout = 10s' >> /usr/local/etc/php-fpm.d/www.conf

# Copy application files
COPY . /var/www/html/

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && find /var/www/html -type f -exec chmod 644 {} \;

# Configure Nginx
RUN echo 'server { \n\
    listen 80; \n\
    server_name _; \n\
    root /var/www/html; \n\
    index index.php index.html index.htm; \n\
    client_max_body_size 50M; \n\
    \n\
    location / { \n\
        try_files $uri $uri/ /index.php?$query_string; \n\
    } \n\
    \n\
    location = /favicon.ico { \n\
        access_log off; \n\
        log_not_found off; \n\
    } \n\
    \n\
    location ~ \.php$ { \n\
        include fastcgi_params; \n\
        fastcgi_pass 127.0.0.1:9000; \n\
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; \n\
        fastcgi_connect_timeout 60s; \n\
        fastcgi_send_timeout 60s; \n\
        fastcgi_read_timeout 60s; \n\
    } \n\
    \n\
    location ~ /\.ht { \n\
        deny all; \n\
    } \n\
}' > /etc/nginx/sites-available/default

# Expose port 80
EXPOSE 80

# Start PHP-FPM and Nginx
CMD php-fpm -D && nginx -g 'daemon off;'