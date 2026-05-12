FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    curl \
    unzip \
    libcurl4-openssl-dev \
    libxml2-dev \
    libpng-dev \
    libzip-dev \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mysqli \
        curl \
        xml \
        zip \
    && rm -rf /var/lib/apt/lists/*

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Enable Apache mod_rewrite for clean URLs
RUN a2enmod rewrite

# Update Apache config to allow .htaccess overrides
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Set working directory
WORKDIR /var/www/html

# --- NEW: Cloud Deployment Steps ---

# 1. Copy your project files into the container
COPY . /var/www/html

# 2. Run composer install to pull in PHPMailer and SimplePie
# We use --no-dev to keep the image small and fast
RUN composer install --working-dir=backend --no-interaction --no-plugins --no-scripts --prefer-dist

# 3. Set proper permissions for the web server
RUN chown -R www-data:www-data /var/www/html

# Standard Apache start
CMD ["apache2-foreground"]