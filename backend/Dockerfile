FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Configure PHP-FPM
RUN sed -i 's/listen = .*/listen = 0.0.0.0:9000/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/;clear_env = no/clear_env = no/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/user = www-data/user = www-data/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/group = www-data/group = www-data/' /usr/local/etc/php-fpm.d/www.conf

# Create entrypoint script to fix permissions
RUN echo '#!/bin/bash\n\
set -e\n\
\n\
# Fix permissions for storage and bootstrap/cache\n\
if [ -d "/var/www/html/storage" ]; then\n\
    chown -R www-data:www-data /var/www/html/storage 2>/dev/null || true\n\
    chmod -R 775 /var/www/html/storage\n\
    find /var/www/html/storage -type d -exec chmod 775 {} \\;\n\
    find /var/www/html/storage -type f -exec chmod 664 {} \\;\n\
fi\n\
\n\
if [ -d "/var/www/html/bootstrap/cache" ]; then\n\
    chown -R www-data:www-data /var/www/html/bootstrap/cache 2>/dev/null || true\n\
    chmod -R 775 /var/www/html/bootstrap/cache\n\
    find /var/www/html/bootstrap/cache -type d -exec chmod 775 {} \\;\n\
    find /var/www/html/bootstrap/cache -type f -exec chmod 664 {} \\;\n\
fi\n\
\n\
# Execute original command\n\
exec "$@"' > /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

# Expose port 9000 (PHP-FPM)
EXPOSE 9000

# Use entrypoint to fix permissions before starting PHP-FPM
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["php-fpm"]
