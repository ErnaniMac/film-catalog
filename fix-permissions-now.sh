#!/bin/bash
# Script para corrigir permissões AGORA (requer sudo)
set -e

HOST_UID=$(id -u)
HOST_GID=$(id -g)

echo "Corrigindo permissões para usuário $HOST_UID:$HOST_GID..."

# Frontend
if [ -d "frontend" ]; then
    echo "Corrigindo frontend..."
    sudo chown -R $HOST_UID:$HOST_GID frontend/
    find frontend/ -type d -exec chmod 755 {} \;
    find frontend/ -type f ! -path "*/node_modules/*" -exec chmod 644 {} \;
    
    if [ -d "frontend/node_modules" ]; then
        sudo chown -R $HOST_UID:$HOST_GID frontend/node_modules/
        find frontend/node_modules/ -type d -exec chmod 755 {} \;
        find frontend/node_modules/ -type f -path "*/bin/*" -exec chmod 755 {} \;
        find frontend/node_modules/ -type f -path "*/.bin/*" -exec chmod 755 {} \;
        find frontend/node_modules/ -type f ! -path "*/.bin/*" ! -path "*/bin/*" -exec chmod 644 {} \;
    fi
fi

# Backend
if [ -d "backend" ]; then
    echo "Corrigindo backend..."
    sudo chown -R $HOST_UID:$HOST_GID backend/
    find backend/ -type d -exec chmod 755 {} \;
    find backend/ -type f -exec chmod 644 {} \;
    [ -d "backend/storage" ] && chmod -R 775 backend/storage
    [ -d "backend/bootstrap/cache" ] && chmod -R 775 backend/bootstrap/cache
fi

echo "✓ Permissões corrigidas!"
