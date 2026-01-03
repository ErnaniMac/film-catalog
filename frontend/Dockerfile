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

# Get UID/GID from environment or use defaults
HOST_UID=${HOST_UID:-1000}
HOST_GID=${HOST_GID:-1000}

# Fix ownership of app directory if running as root
if [ "$(id -u)" = "0" ] && [ -d "/app" ]; then
    # Use numeric IDs to avoid issues with user/group names
    # This ensures files created in the volume have correct ownership on host
    # Force ownership correction - this affects files on the host too
    echo "Corrigindo permissões do /app para UID=${HOST_UID} GID=${HOST_GID}..."
    chown -R ${HOST_UID}:${HOST_GID} /app 2>/dev/null || true
    
    # Set proper permissions - directories need execute bit and write permission for group
    find /app -type d -exec chmod 775 {} \; 2>/dev/null || true
    
    # Files should be readable/writable by owner and group
    find /app -type f ! -path "*/node_modules/*" -exec chmod 664 {} \; 2>/dev/null || true
    
    # Ensure node_modules directory exists and is writable
    if [ -d "/app/node_modules" ]; then
        chown -R ${HOST_UID}:${HOST_GID} /app/node_modules 2>/dev/null || true
        find /app/node_modules -type d -exec chmod 755 {} \; 2>/dev/null || true
        
        # PRIMEIRO: dar permissão de execução aos binários
        # Corrigir permissões dos arquivos de destino dos links simbólicos (binários JS)
        find /app/node_modules -type f -path "*/bin/*" -exec chmod 755 {} \; 2>/dev/null || true
        find /app/node_modules -type f -path "*/.bin/*" -exec chmod 755 {} \; 2>/dev/null || true
        # Corrigir links simbólicos e arquivos executáveis em .bin
        if [ -d "/app/node_modules/.bin" ]; then
            find /app/node_modules/.bin -type l -exec chmod 755 {} \; 2>/dev/null || true
            find /app/node_modules/.bin -type f -exec chmod 755 {} \; 2>/dev/null || true
        fi
        # DEPOIS: aplicar chmod 644 apenas em arquivos que NÃO são binários
        find /app/node_modules -type f ! -path "*/.bin/*" ! -path "*/bin/*" -exec chmod 644 {} \; 2>/dev/null || true
        # GARANTIR: aplicar chmod 755 novamente nos binários DEPOIS do chmod 644 (para garantir)
        find /app/node_modules -type f -path "*/bin/*" -exec chmod 755 {} \; 2>/dev/null || true
        find /app/node_modules -type f -path "*/.bin/*" -exec chmod 755 {} \; 2>/dev/null || true
    fi
    
    # Make sure package files are writable
    [ -f "/app/package.json" ] && chown ${HOST_UID}:${HOST_GID} /app/package.json && chmod 664 /app/package.json 2>/dev/null || true
    [ -f "/app/package-lock.json" ] && chown ${HOST_UID}:${HOST_GID} /app/package-lock.json && chmod 664 /app/package-lock.json 2>/dev/null || true
    [ -f "/app/vite.config.js" ] && chown ${HOST_UID}:${HOST_GID} /app/vite.config.js && chmod 664 /app/vite.config.js 2>/dev/null || true
    
    # Fix src directory permissions - directories 775, files 664
    [ -d "/app/src" ] && chown -R ${HOST_UID}:${HOST_GID} /app/src && find /app/src -type d -exec chmod 775 {} \; && find /app/src -type f -exec chmod 664 {} \; 2>/dev/null || true
    
    # Clean up any Vite timestamp files that might have wrong permissions
    find /app -name "*.timestamp-*.mjs" -exec chown ${HOST_UID}:${HOST_GID} {} \; -exec chmod 664 {} \; 2>/dev/null || true
    find /app -name "vite.config.js.timestamp-*" -exec chown ${HOST_UID}:${HOST_GID} {} \; -exec chmod 664 {} \; 2>/dev/null || true
fi

# Fix binary permissions one more time before switching to appuser
if [ "$(id -u)" = "0" ] && [ -d "/app/node_modules" ]; then
    find /app/node_modules -type f -path "*/bin/*" -exec chmod 755 {} \; 2>/dev/null || true
    find /app/node_modules -type f -path "*/.bin/*" -exec chmod 755 {} \; 2>/dev/null || true
fi

# Switch to appuser and execute command
# Use numeric IDs to ensure correct user
if [ "$(id -u)" = "0" ]; then
    # Ensure appuser exists with correct UID/GID
    if ! id -u appuser >/dev/null 2>&1; then
        adduser -u ${HOST_UID} -G appuser -D -s /bin/sh appuser 2>/dev/null || true
    fi
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

# Create startup script that fixes permissions and starts npm
# This script will be executed as appuser (after entrypoint switches)
RUN echo '#!/bin/sh' > /usr/local/bin/start-dev.sh && \
    echo 'set -e' >> /usr/local/bin/start-dev.sh && \
    echo '# Fix permissions of binaries before starting' >> /usr/local/bin/start-dev.sh && \
    echo 'if [ -d "/app/node_modules" ]; then' >> /usr/local/bin/start-dev.sh && \
    echo '  find /app/node_modules -type f -path "*/bin/*" -exec chmod 755 {} \; 2>/dev/null || true' >> /usr/local/bin/start-dev.sh && \
    echo '  find /app/node_modules -type f -path "*/.bin/*" -exec chmod 755 {} \; 2>/dev/null || true' >> /usr/local/bin/start-dev.sh && \
    echo 'fi' >> /usr/local/bin/start-dev.sh && \
    echo '# Clean up any existing Vite timestamp files with wrong permissions' >> /usr/local/bin/start-dev.sh && \
    echo 'find /app -name "*.timestamp-*.mjs" -delete 2>/dev/null || true' >> /usr/local/bin/start-dev.sh && \
    echo 'find /app -name "vite.config.js.timestamp-*" -delete 2>/dev/null || true' >> /usr/local/bin/start-dev.sh && \
    echo '# Ensure new files are created with correct ownership (group writable)' >> /usr/local/bin/start-dev.sh && \
    echo 'umask 0002' >> /usr/local/bin/start-dev.sh && \
    echo 'exec npm run dev -- --host 0.0.0.0' >> /usr/local/bin/start-dev.sh && \
    chmod +x /usr/local/bin/start-dev.sh

# Start development server
CMD ["/usr/local/bin/start-dev.sh"]
