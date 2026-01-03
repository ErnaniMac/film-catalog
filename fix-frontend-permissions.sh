#!/bin/bash
# Script para corrigir permissões do frontend no host
# Uso: ./fix-frontend-permissions.sh

set -e

# Carregar .env.docker se existir
if [ -f ".env.docker" ]; then
    source .env.docker
fi

# Obter UID/GID do usuário atual ou do .env.docker
HOST_UID=${HOST_UID:-$(id -u)}
HOST_GID=${HOST_GID:-$(id -g)}

echo "Corrigindo permissões do frontend para usuário $HOST_UID:$HOST_GID..."

if [ -d "frontend" ]; then
    # Corrigir propriedade de todos os arquivos
    sudo chown -R ${HOST_UID}:${HOST_GID} frontend/
    
    # Corrigir permissões de diretórios
    sudo find frontend/ -type d -exec chmod 755 {} \;
    
    # Corrigir permissões de arquivos (exceto node_modules)
    sudo find frontend/ -type f ! -path "*/node_modules/*" -exec chmod 644 {} \;
    
    # node_modules precisa de permissões especiais
    if [ -d "frontend/node_modules" ]; then
        echo "Corrigindo permissões do node_modules..."
        sudo chown -R ${HOST_UID}:${HOST_GID} frontend/node_modules/
        sudo find frontend/node_modules/ -type d -exec chmod 755 {} \;
        # PRIMEIRO: dar permissão de execução aos binários
        sudo find frontend/node_modules/ -type f -path "*/bin/*" -exec chmod 755 {} \;
        sudo find frontend/node_modules/ -type f -path "*/.bin/*" -exec chmod 755 {} \;
        # DEPOIS: aplicar chmod 644 apenas em arquivos que NÃO são binários
        sudo find frontend/node_modules/ -type f ! -path "*/.bin/*" ! -path "*/bin/*" -exec chmod 644 {} \;
    fi
    
    echo "Permissões do frontend corrigidas!"
    echo ""
    echo "Agora você pode reiniciar o container:"
    echo "  source .env.docker && docker-compose restart node"
else
    echo "Diretório frontend não encontrado!"
    exit 1
fi
