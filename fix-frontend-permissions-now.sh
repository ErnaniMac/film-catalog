#!/bin/bash
# Script rápido para corrigir permissões do frontend
# Uso: ./fix-frontend-permissions-now.sh

set -e

if [ -f ".env.docker" ]; then
    source .env.docker
fi

HOST_UID=${HOST_UID:-$(id -u)}
HOST_GID=${HOST_GID:-$(id -g)}

echo "Corrigindo permissões do frontend para $HOST_UID:$HOST_GID..."

if [ -d "frontend" ]; then
    sudo chown -R ${HOST_UID}:${HOST_GID} frontend/
    sudo find frontend/ -type d -exec chmod 755 {} \;
    sudo find frontend/ -type f ! -path "*/node_modules/*" -exec chmod 644 {} \;
    
    if [ -d "frontend/node_modules" ]; then
        sudo chown -R ${HOST_UID}:${HOST_GID} frontend/node_modules/
        sudo find frontend/node_modules/ -type d -exec chmod 755 {} \;
        sudo find frontend/node_modules/ -type f -path "*/bin/*" -exec chmod 755 {} \;
        sudo find frontend/node_modules/ -type f -path "*/.bin/*" -exec chmod 755 {} \;
        sudo find frontend/node_modules/ -type f ! -path "*/.bin/*" ! -path "*/bin/*" -exec chmod 644 {} \;
    fi
    
    echo "✅ Permissões corrigidas!"
    echo ""
    echo "Reinicie o container para aplicar:"
    echo "  source .env.docker && docker-compose restart node"
else
    echo "❌ Diretório frontend não encontrado!"
    exit 1
fi
