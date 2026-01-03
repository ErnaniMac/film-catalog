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
    find frontend/ -type f -exec chmod 644 {} \;
    
    # node_modules é gerenciado pelo container, mas se existir, garantir permissões corretas
    if [ -d "frontend/node_modules" ]; then
        echo "Corrigindo permissões do node_modules..."
        sudo chown -R $HOST_UID:$HOST_GID frontend/node_modules/
        find frontend/node_modules/ -type d -exec chmod 755 {} \;
        find frontend/node_modules/ -type f -exec chmod 644 {} \;
        # Binários do node_modules precisam de permissão de execução
        if [ -d "frontend/node_modules/.bin" ]; then
            find frontend/node_modules/.bin -type f -exec chmod 755 {} \;
        fi
    fi
    
    echo "Permissões do frontend corrigidas!"
else
    echo "Diretório frontend não encontrado!"
    exit 1
fi

