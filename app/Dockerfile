# app/Dockerfile
FROM php:8.1-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql # Install MySQLi extension

# Enable Apache rewrite module if needed (not strictly for this example but good practice)
# RUN a2enmod rewrite

# Copy your application code into the container
COPY . /var/www/html

# Set appropriate permissions (important for web servers)
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

# Expose port 80 (Apache's default HTTP port)
EXPOSE 80