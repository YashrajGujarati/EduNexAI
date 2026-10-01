#!/bin/bash
set -e

# Railway dynamically injects PORT. Default to 80 if not set.
PORT="${PORT:-80}"

echo "Starting EduNexAI Backend on port ${PORT}..."

# Update Apache port binding dynamically to listen on Railway's $PORT
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:${PORT}>/g" /etc/apache2/sites-available/000-default.conf

# Ensure writable directories exist for reports and file uploads
mkdir -p /var/www/html/python/output /var/www/html/uploads/assignments /var/www/html/uploads/submissions
chown -R www-data:www-data /var/www/html/python/output /var/www/html/uploads
chmod -R 775 /var/www/html/python/output /var/www/html/uploads

# Start Apache in the foreground
exec apache2-foreground
