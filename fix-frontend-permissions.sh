#!/bin/bash
# Script para corrigir permissões do frontend manualmente
# Uso: ./fix-frontend-permissions.sh

set -e

HOST_UID=$(id -u)
HOST_GID=$(id -g)

echo "Corrigindo permissões do frontend para usuário $HOST_UID:$HOST_GID..."

if [ -d "frontend" ]; then
    # Corrigir propriedade de todos os arquivos
    sudo chown -R $HOST_UID:$HOST_GID frontend/
    
    # Corrigir permissões
    find frontend/ -type d -exec chmod 755 {} \;
    find frontend/ -type f -exec chmod 644 {} \;
    
    # Excluir node_modules do chown (é gerenciado pelo container)
    echo "Permissões do frontend corrigidas!"
    echo "Nota: node_modules é gerenciado pelo container Docker"
else
    echo "Diretório frontend não encontrado!"
    exit 1
fi

