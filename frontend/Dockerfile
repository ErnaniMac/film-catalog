FROM node:18-alpine

# Build arguments for user/group IDs
ARG USER_ID=1000
ARG GROUP_ID=1000

# Create user with same UID/GID as host user (Alpine uses addgroup/adduser)
RUN addgroup -g ${GROUP_ID} -S appuser || true \
    && adduser -u ${USER_ID} -G appuser -S -s /bin/sh appuser || true \
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
