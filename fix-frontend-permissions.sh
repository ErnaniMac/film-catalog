#!/bin/bash
# Script para corrigir permissões do frontend manualmente
# Uso: ./fix-frontend-permissions.sh

set -e

HOST_UID=$(id -u)
HOST_GID=$(id -g)

echo "Corrigindo permissões do frontend para usuário $HOST_UID:$HOST_GID..."

if [ -d "frontend" ]; then
    # Corrigir propriedade de todos os arquivos (exceto node_modules)
    sudo chown -R $HOST_UID:$HOST_GID frontend/
    
    # Corrigir permissões de diretórios e arquivos
    find frontend/ -type d -exec chmod 755 {} \;
    find frontend/ -type f ! -path "*/node_modules/*" -exec chmod 644 {} \;
    
    # node_modules precisa de permissões especiais
    if [ -d "frontend/node_modules" ]; then
        echo "Corrigindo permissões do node_modules..."
        sudo chown -R $HOST_UID:$HOST_GID frontend/node_modules/
        find frontend/node_modules/ -type d -exec chmod 755 {} \;
        # PRIMEIRO: dar permissão de execução aos binários
        find frontend/node_modules/ -type f -path "*/bin/*" -exec chmod 755 {} \;
        find frontend/node_modules/ -type f -path "*/.bin/*" -exec chmod 755 {} \;
        # DEPOIS: aplicar chmod 644 apenas em arquivos que NÃO são binários
        find frontend/node_modules/ -type f ! -path "*/.bin/*" ! -path "*/bin/*" -exec chmod 644 {} \;
    fi
    
    echo "Permissões do frontend corrigidas!"
else
    echo "Diretório frontend não encontrado!"
    exit 1
fi

