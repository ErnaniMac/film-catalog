FROM node:18-alpine

# Build arguments for user/group IDs
ARG USER_ID=1000
ARG GROUP_ID=1000

# Install sudo and shadow for user management
RUN apk add --no-cache sudo shadow

# Create user with same UID/GID as host user
RUN groupadd -g ${GROUP_ID} appuser || true \
    && useradd -u ${USER_ID} -g ${GROUP_ID} -m -s /bin/sh appuser || true \
    && mkdir -p /app \
    && chown -R appuser:appuser /app

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
