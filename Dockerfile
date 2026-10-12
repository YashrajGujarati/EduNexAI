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
    libssl-dev \
    pkg-config \
    zip \
    unzip \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Symlink python3 to python so PHP shell_exec('python ...') works
RUN ln -sf /usr/bin/python3 /usr/bin/python

# Configure PHP extensions required by the application
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install mysqli pdo pdo_mysql gd zip \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb

# PHP's Apache image requires the prefork MPM. Disable other MPM modules so
# Apache does not fail when a base image or platform enables one by default.
RUN a2dismod mpm_event mpm_worker 2>/dev/null || true \
    && a2enmod mpm_prefork rewrite

# Install Python packages required by the analytics scripts
RUN pip3 install --no-cache-dir --break-system-packages \
    pandas \
    scikit-learn \
    mysql-connector-python \
    pymongo \
    seaborn \
    matplotlib

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . /var/www/html/

RUN if [ -f "composer.json" ]; then composer install --no-dev --optimize-autoloader; fi

RUN mkdir -p /var/www/html/python/output \
             /var/www/html/uploads/assignments \
             /var/www/html/uploads/submissions \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/python/output /var/www/html/uploads

COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

CMD ["docker-entrypoint.sh"]
