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
# Configure error_log in php-fpm.conf (main config, not pool)
RUN sed -i 's/listen = .*/listen = 0.0.0.0:9000/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/;clear_env = no/clear_env = no/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i "s/user = www-data/user = appuser/" /usr/local/etc/php-fpm.d/www.conf \
    && sed -i "s/group = www-data/group = appuser/" /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's|^;error_log =.*|error_log = /var/www/html/storage/logs/php-fpm.log|' /usr/local/etc/php-fpm.conf \
    && sed -i 's|^error_log =.*|error_log = /var/www/html/storage/logs/php-fpm.log|' /usr/local/etc/php-fpm.conf

# Create entrypoint script to fix permissions and ownership
# Container runs as root, entrypoint fixes permissions then switches to appuser
RUN echo '#!/bin/bash\n\
set -e\n\
\n\
# Ensure storage directories exist\n\
if [ -d "/var/www/html" ]; then\n\
    mkdir -p /var/www/html/storage/framework/{sessions,views,cache/data} 2>/dev/null || true\n\
    mkdir -p /var/www/html/storage/logs 2>/dev/null || true\n\
    mkdir -p /var/www/html/storage/app/{public,private} 2>/dev/null || true\n\
    mkdir -p /var/www/html/bootstrap/cache 2>/dev/null || true\n\
    \n\
    # Only change ownership if we'\''re root\n\
    if [ "$(id -u)" = "0" ]; then\n\
        # Use numeric IDs to avoid issues\n\
        chown -R appuser:appuser /var/www/html/storage 2>/dev/null || true\n\
        chown -R appuser:appuser /var/www/html/bootstrap/cache 2>/dev/null || true\n\
        \n\
        # Set permissions\n\
        find /var/www/html/storage -type d -exec chmod 775 {} \\; 2>/dev/null || true\n\
        find /var/www/html/storage -type f -exec chmod 664 {} \\; 2>/dev/null || true\n\
        find /var/www/html/bootstrap/cache -type d -exec chmod 775 {} \\; 2>/dev/null || true\n\
        find /var/www/html/bootstrap/cache -type f -exec chmod 664 {} \\; 2>/dev/null || true\n\
        \n\
    fi\n\
fi\n\
\n\
# Switch to appuser and execute command\n\
# PHP-FPM will run as appuser\n\
if [ "$(id -u)" = "0" ]; then\n\
    exec gosu appuser "$@"\n\
else\n\
    exec "$@"\n\
fi' > /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

# Install gosu for user switching
RUN apt-get update && apt-get install -y gosu && rm -rf /var/lib/apt/lists/*

# Expose port 9000 (PHP-FPM)
EXPOSE 9000

# Use entrypoint to fix permissions before starting PHP-FPM
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["php-fpm"]
