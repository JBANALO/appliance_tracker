FROM php:8.2-apache

# Install required PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy application files FIRST
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Remove ALL Apache MPM modules and reinstall only prefork
RUN apt-get update && \
    apt-get remove -y apache2-bin && \
    apt-get install -y apache2 && \
    a2dismod mpm_event mpm_worker && \
    a2enmod mpm_prefork rewrite

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]