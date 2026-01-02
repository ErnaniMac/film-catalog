FROM php:8.4-fpm

# Build arguments for user/group IDs
ARG USER_ID=1000
ARG GROUP_ID=1000

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

# Create user with same UID/GID as host user
RUN groupadd -g ${GROUP_ID} appuser || true \
    && useradd -u ${USER_ID} -g ${GROUP_ID} -m -s /bin/bash appuser || true

# Set working directory
WORKDIR /var/www/html

# Configure PHP-FPM to use appuser
RUN sed -i 's/listen = .*/listen = 0.0.0.0:9000/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/;clear_env = no/clear_env = no/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i "s/user = www-data/user = appuser/" /usr/local/etc/php-fpm.d/www.conf \
    && sed -i "s/group = www-data/group = appuser/" /usr/local/etc/php-fpm.d/www.conf

# Create entrypoint script to fix permissions and ownership
RUN echo '#!/bin/bash\n\
set -e\n\
\n\
# Fix ownership of entire application directory (only if mounted)\n\
# This ensures files created inside container belong to appuser\n\
if [ -d "/var/www/html" ]; then\n\
    # Fix ownership for storage and bootstrap/cache (must be writable)\n\
    if [ -d "/var/www/html/storage" ]; then\n\
        chown -R appuser:appuser /var/www/html/storage 2>/dev/null || true\n\
        chmod -R 775 /var/www/html/storage\n\
        find /var/www/html/storage -type d -exec chmod 775 {} \\; 2>/dev/null || true\n\
        find /var/www/html/storage -type f -exec chmod 664 {} \\; 2>/dev/null || true\n\
    fi\n\
    \n\
    if [ -d "/var/www/html/bootstrap/cache" ]; then\n\
        chown -R appuser:appuser /var/www/html/bootstrap/cache 2>/dev/null || true\n\
        chmod -R 775 /var/www/html/bootstrap/cache\n\
        find /var/www/html/bootstrap/cache -type d -exec chmod 775 {} \\; 2>/dev/null || true\n\
        find /var/www/html/bootstrap/cache -type f -exec chmod 664 {} \\; 2>/dev/null || true\n\
    fi\n\
    \n\
    # Ensure appuser owns vendor if it exists\n\
    [ -d "/var/www/html/vendor" ] && chown -R appuser:appuser /var/www/html/vendor 2>/dev/null || true\n\
fi\n\
\n\
# Execute original command as appuser\n\
exec gosu appuser "$@"' > /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

# Install gosu for user switching
RUN apt-get update && apt-get install -y gosu && rm -rf /var/lib/apt/lists/*

# Expose port 9000 (PHP-FPM)
EXPOSE 9000

# Use entrypoint to fix permissions before starting PHP-FPM
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["php-fpm"]
