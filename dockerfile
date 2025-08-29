FROM php:8.2.4-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    mysql-client \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd mysqli \
    && docker-php-ext-enable pdo_mysql mbstring exif pcntl bcmath gd mysqli

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Enable Apache mod_rewrite for Laravel
RUN a2enmod rewrite

# Remove the existing 000-default.conf file
RUN rm /etc/apache2/sites-available/000-default.conf

# Create a new 000-default.conf with the correct DocumentRoot
RUN echo '<VirtualHost *:8080>\n\
    ServerAdmin webmaster@localhost\n\
    DocumentRoot /var/www/tools.africacdc.org/tools/public_dashboards\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
    <Directory /var/www/tools.africacdc.org/tools/public_dashboards>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
    </Directory>\n\
    </VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Set working directory
WORKDIR /var/www/tools.africacdc.org/tools/public_dashboards

# Expose port 8080
EXPOSE 8080
