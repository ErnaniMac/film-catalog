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
RUN echo '#!/bin/sh\n\
set -e\n\
\n\
# Fix ownership of app directory if running as root\n\
if [ "$(id -u)" = "0" ] && [ -d "/app" ]; then\n\
    chown -R appuser:appuser /app 2>/dev/null || true\n\
    find /app -type d -exec chmod 755 {} \\; 2>/dev/null || true\n\
    find /app -type f -exec chmod 644 {} \\; 2>/dev/null || true\n\
    \n\
    # Ensure node_modules directory exists and is writable\n\
    mkdir -p /app/node_modules 2>/dev/null || true\n\
    chown -R appuser:appuser /app/node_modules 2>/dev/null || true\n\
    chmod -R 755 /app/node_modules 2>/dev/null || true\n\
fi\n\
\n\
# Switch to appuser and execute command\n\
if [ "$(id -u)" = "0" ]; then\n\
    exec su-exec appuser "$@"\n\
else\n\
    exec "$@"\n\
fi' > /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

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
