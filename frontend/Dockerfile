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

WORKDIR /app

# Switch to appuser for npm operations
USER appuser

# Copy package files
COPY --chown=appuser:appuser package*.json ./

# Install dependencies
RUN npm install

# Copy application files
COPY --chown=appuser:appuser . .

# Expose port
EXPOSE 5173

# Start development server
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]
