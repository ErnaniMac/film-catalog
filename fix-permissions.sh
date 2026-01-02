#!/bin/bash
# Script to fix file permissions after Docker operations
# Usage: ./fix-permissions.sh

set -e

# Get current user ID and group ID
USER_ID=$(id -u)
GROUP_ID=$(id -g)

echo "Fixing permissions for user $USER_ID:$GROUP_ID..."

# Fix backend permissions
if [ -d "backend" ]; then
    echo "Fixing backend permissions..."
    sudo chown -R $USER_ID:$GROUP_ID backend/
    find backend/ -type d -exec chmod 755 {} \;
    find backend/ -type f -exec chmod 644 {} \;
    
    # Special permissions for Laravel directories
    [ -d "backend/storage" ] && chmod -R 775 backend/storage
    [ -d "backend/bootstrap/cache" ] && chmod -R 775 backend/bootstrap/cache
fi

# Fix frontend permissions
if [ -d "frontend" ]; then
    echo "Fixing frontend permissions..."
    sudo chown -R $USER_ID:$GROUP_ID frontend/
    find frontend/ -type d -exec chmod 755 {} \;
    find frontend/ -type f -exec chmod 644 {} \;
fi

echo "Permissions fixed!"

