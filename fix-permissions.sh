#!/bin/bash
# Script to fix file permissions after Docker operations
# Usage: ./fix-permissions.sh

set -e

# Get current user ID and group ID
HOST_UID=$(id -u)
HOST_GID=$(id -g)

echo "Fixing permissions for user $HOST_UID:$HOST_GID..."

# Fix backend permissions
if [ -d "backend" ]; then
    echo "Fixing backend permissions..."
    sudo chown -R $HOST_UID:$HOST_GID backend/
    find backend/ -type d -exec chmod 755 {} \;
    find backend/ -type f -exec chmod 644 {} \;
    
    # Special permissions for Laravel directories
    [ -d "backend/storage" ] && chmod -R 775 backend/storage
    [ -d "backend/bootstrap/cache" ] && chmod -R 775 backend/bootstrap/cache
fi

# Fix frontend permissions
if [ -d "frontend" ]; then
    echo "Fixing frontend permissions..."
    sudo chown -R $HOST_UID:$HOST_GID frontend/
    find frontend/ -type d -exec chmod 755 {} \;
    find frontend/ -type f -exec chmod 644 {} \;
    
    # node_modules precisa de permissões especiais
    if [ -d "frontend/node_modules" ]; then
        echo "Fixing node_modules permissions..."
        sudo chown -R $HOST_UID:$HOST_GID frontend/node_modules/
        find frontend/node_modules/ -type d -exec chmod 755 {} \;
        find frontend/node_modules/ -type f -exec chmod 644 {} \;
        # Binários do node_modules precisam de permissão de execução
        if [ -d "frontend/node_modules/.bin" ]; then
            find frontend/node_modules/.bin -type f -exec chmod 755 {} \;
        fi
    fi
fi

echo "Permissions fixed!"

