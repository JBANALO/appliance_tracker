FROM php:8.2-fpm

# Install nginx and required PHP extensions
RUN apt-get update && apt-get install -y \
    nginx \
    && docker-php-ext-install mysqli pdo pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Copy application files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Configure Nginx
RUN echo 'server { \n\
    listen 80; \n\
    server_name _; \n\
    root /var/www/html; \n\
    index index.php index.html; \n\
    \n\
    location / { \n\
        try_files $uri $uri/ /index.php?$query_string; \n\
    } \n\
    \n\
    location ~ \.php$ { \n\
        fastcgi_pass 127.0.0.1:9000; \n\
        fastcgi_index index.php; \n\
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; \n\
        include fastcgi_params; \n\
    } \n\
}' > /etc/nginx/sites-available/default

# Expose port 80
EXPOSE 80

# Start PHP-FPM and Nginx
CMD php-fpm -D && nginx -g 'daemon off;'