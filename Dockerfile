FROM php:8.2-apache

# Install system dependencies and Python environment
RUN apt-get update && apt-get install -y \
    python3 \
    python3-pip \
    python3-venv \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# Symlink python3 to python so PHP `shell_exec('python ...')` works seamlessly
RUN ln -sf /usr/bin/python3 /usr/bin/python

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install mysqli pdo pdo_mysql gd zip

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install Python packages globally or via pip --break-system-packages
RUN pip3 install --no-cache-dir --break-system-packages \
    pandas \
    scikit-learn \
    mysql-connector-python \
    seaborn \
    matplotlib

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Run composer install if composer.json is present
RUN if [ -f "composer.json" ]; then composer install --no-dev --optimize-autoloader; fi

# Create output and upload directories with write permissions
RUN mkdir -p /var/www/html/python/output /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/python/output /var/www/html/uploads

EXPOSE 80

CMD ["apache2-foreground"]
