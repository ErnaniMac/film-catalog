FROM node:18-alpine

# Build arguments for user/group IDs
ARG USER_ID=1000
ARG GROUP_ID=1000

# Get existing group name if GID is already in use
RUN EXISTING_GROUP=$(getent group ${GROUP_ID} 2>/dev/null | cut -d: -f1 || echo ""); \
    if [ -z "$EXISTING_GROUP" ]; then \
        addgroup -g ${GROUP_ID} appuser; \
    else \
        if [ "$EXISTING_GROUP" != "appuser" ]; then \
            addgroup appuser || true; \
            GROUP_NAME=appuser; \
        else \
            GROUP_NAME=appuser; \
        fi; \
    fi; \
    EXISTING_USER=$(getent passwd ${USER_ID} 2>/dev/null | cut -d: -f1 || echo ""); \
    if [ -z "$EXISTING_USER" ]; then \
        adduser -u ${USER_ID} -G appuser -D -s /bin/sh appuser; \
    else \
        if [ "$EXISTING_USER" != "appuser" ]; then \
            adduser -G appuser -D -s /bin/sh appuser || true; \
        fi; \
    fi; \
    mkdir -p /app; \
    chown -R appuser:appuser /app 2>/dev/null || chown -R ${USER_ID}:${GROUP_ID} /app

# Install su-exec for user switching (Alpine alternative to gosu)
RUN apk add --no-cache su-exec

# Create entrypoint script to fix permissions
# This script runs as root and fixes permissions before switching to appuser
RUN cat > /usr/local/bin/docker-entrypoint.sh << 'EOFSCRIPT'
#!/bin/sh
set -e

# Fix ownership of app directory if running as root
if [ "$(id -u)" = "0" ] && [ -d "/app" ]; then
    # Change ownership of all files to appuser (which has same UID/GID as host)
    chown -R appuser:appuser /app 2>/dev/null || true
    
    # Set proper permissions - directories need execute bit
    find /app -type d -exec chmod 755 {} \; 2>/dev/null || true
    find /app -type f -exec chmod 644 {} \; 2>/dev/null || true
    
    # Ensure node_modules directory exists and is writable
    mkdir -p /app/node_modules 2>/dev/null || true
    chown -R appuser:appuser /app/node_modules 2>/dev/null || true
    find /app/node_modules -type d -exec chmod 755 {} \; 2>/dev/null || true
    find /app/node_modules -type f -exec chmod 644 {} \; 2>/dev/null || true
    
    # Make sure package files are writable
    [ -f "/app/package.json" ] && chown appuser:appuser /app/package.json && chmod 644 /app/package.json 2>/dev/null || true
    [ -f "/app/package-lock.json" ] && chown appuser:appuser /app/package-lock.json && chmod 644 /app/package-lock.json 2>/dev/null || true
    [ -f "/app/vite.config.js" ] && chown appuser:appuser /app/vite.config.js && chmod 644 /app/vite.config.js 2>/dev/null || true
    
    # Fix src directory permissions
    [ -d "/app/src" ] && chown -R appuser:appuser /app/src && find /app/src -type d -exec chmod 755 {} \; && find /app/src -type f -exec chmod 644 {} \; 2>/dev/null || true
fi

# Switch to appuser and execute command
if [ "$(id -u)" = "0" ]; then
    exec su-exec appuser "$@"
else
    exec "$@"
fi
EOFSCRIPT
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

WORKDIR /app

# Use entrypoint to fix permissions before switching to appuser
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

# Copy package files
COPY --chown=appuser:appuser package*.json ./

# Install dependencies (will run as appuser via entrypoint)
RUN npm install

# Copy application files
COPY --chown=appuser:appuser . .

# Expose port
EXPOSE 5173

# Start development server
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]
