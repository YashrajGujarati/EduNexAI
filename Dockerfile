FROM php:8.2-apache

# ============================================================
# EduNexAI — PHP/Apache Docker Image for Railway
# ============================================================

# Install system dependencies
RUN apt-get update && apt-get install -y \
    python3 \
    python3-pip \
    python3-venv \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libssl-dev \
    pkg-config \
    zip \
    unzip \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Symlink python3 → python so PHP shell_exec('python ...') works
RUN ln -sf /usr/bin/python3 /usr/bin/python

# Configure and install PHP extensions (mysqli, pdo_mysql, gd, zip)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install mysqli pdo pdo_mysql gd zip

# Install PHP mongodb PECL extension (required for MongoDB\Driver\Manager)
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install Python packages for analytics scripts
RUN pip3 install --no-cache-dir --break-system-packages \
    pandas \
    scikit-learn \
    pymongo \
    seaborn \
    matplotlib

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Run composer install for PHP dependencies (phpoffice/phpspreadsheet etc.)
RUN if [ -f "composer.json" ]; then composer install --no-dev --optimize-autoloader; fi

# Create writable output and upload directories
RUN mkdir -p /var/www/html/python/output \
             /var/www/html/uploads/assignments \
             /var/www/html/uploads/submissions \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/python/output /var/www/html/uploads

# Copy and set up entrypoint script for Railway PORT binding
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

CMD ["docker-entrypoint.sh"]
