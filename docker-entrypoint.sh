#!/bin/bash
set -e

# ============================================================
# EduNexAI — Docker Entrypoint for Railway
# Railway dynamically injects PORT. Default to 80 if not set.
# ============================================================

PORT="${PORT:-80}"

echo "Starting EduNexAI Backend on port ${PORT}..."

# Keep only Apache's prefork MPM, which is required by PHP's Apache image.
# Remove enabled module links at runtime as well as during the image build.
find /etc/apache2/mods-enabled -maxdepth 1 \( -name 'mpm_*.load' -o -name 'mpm_*.conf' \) -delete
a2enmod mpm_prefork >/dev/null

# Update Apache port binding dynamically to listen on Railway's $PORT
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/g" /etc/apache2/sites-available/000-default.conf

# Ensure writable directories exist for reports and file uploads
mkdir -p /var/www/html/python/output \
         /var/www/html/uploads/assignments \
         /var/www/html/uploads/submissions

chown -R www-data:www-data /var/www/html/python/output /var/www/html/uploads
chmod -R 775 /var/www/html/python/output /var/www/html/uploads

echo "Apache starting on port ${PORT}..."

# Start Apache in the foreground
exec apache2-foreground
