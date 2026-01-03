#!/bin/bash
# Script consolidado para corrigir permissões do backend e frontend
# Uso: ./fix-permissions.sh
#
# Este script corrige as permissões de todos os arquivos para o usuário atual,
# garantindo que você possa salvar arquivos sem precisar de sudo.

set -e

# Carregar .env.docker se existir (para usar HOST_UID/HOST_GID configurados)
if [ -f ".env.docker" ]; then
    source .env.docker
fi

# Obter UID/GID do usuário atual ou do .env.docker
HOST_UID=${HOST_UID:-$(id -u)}
HOST_GID=${HOST_GID:-$(id -g)}

echo "=========================================="
echo "Corrigindo permissões para $HOST_UID:$HOST_GID"
echo "=========================================="
echo ""

# ============================================
# BACKEND (Laravel)
# ============================================
if [ -d "backend" ]; then
    echo "📁 Corrigindo backend..."
    
    # Corrigir ownership de todos os arquivos
    sudo chown -R ${HOST_UID}:${HOST_GID} backend/
    
    # Diretórios: 755 (rwxr-xr-x)
    sudo find backend/ -type d -exec chmod 755 {} \;
    
    # Arquivos: 644 (rw-r--r--)
    sudo find backend/ -type f -exec chmod 644 {} \;
    
    # Laravel precisa de permissões especiais para storage e cache
    if [ -d "backend/storage" ]; then
        echo "  → Corrigindo storage (775)..."
        sudo chown -R ${HOST_UID}:${HOST_GID} backend/storage/
        sudo find backend/storage/ -type d -exec chmod 775 {} \;
        sudo find backend/storage/ -type f -exec chmod 664 {} \;
    fi
    
    if [ -d "backend/bootstrap/cache" ]; then
        echo "  → Corrigindo bootstrap/cache (775)..."
        sudo chown -R ${HOST_UID}:${HOST_GID} backend/bootstrap/cache/
        sudo find backend/bootstrap/cache/ -type d -exec chmod 775 {} \;
        sudo find backend/bootstrap/cache/ -type f -exec chmod 664 {} \;
    fi
    
    echo "  ✅ Backend corrigido!"
    echo ""
else
    echo "  ⚠️  Diretório backend não encontrado"
    echo ""
fi

# ============================================
# FRONTEND (Vue.js)
# ============================================
if [ -d "frontend" ]; then
    echo "📁 Corrigindo frontend..."
    
    # Corrigir ownership de todos os arquivos
    sudo chown -R ${HOST_UID}:${HOST_GID} frontend/
    
    # Diretórios: 775 (rwxrwxr-x) - group writable para permitir criação de arquivos
    sudo find frontend/ -type d ! -path "*/node_modules/*" -exec chmod 775 {} \;
    
    # Arquivos: 664 (rw-rw-r--) - group writable
    sudo find frontend/ -type f ! -path "*/node_modules/*" -exec chmod 664 {} \;
    
    # node_modules precisa de permissões especiais
    if [ -d "frontend/node_modules" ]; then
        echo "  → Corrigindo node_modules..."
        
        # Ownership
        sudo chown -R ${HOST_UID}:${HOST_GID} frontend/node_modules/
        
        # Diretórios: 755
        sudo find frontend/node_modules/ -type d -exec chmod 755 {} \;
        
        # PRIMEIRO: dar permissão de execução aos binários
        sudo find frontend/node_modules/ -type f -path "*/bin/*" -exec chmod 755 {} \;
        sudo find frontend/node_modules/ -type f -path "*/.bin/*" -exec chmod 755 {} \;
        
        # Links simbólicos em .bin
        if [ -d "frontend/node_modules/.bin" ]; then
            sudo find frontend/node_modules/.bin -type l -exec chmod 755 {} \; 2>/dev/null || true
            sudo find frontend/node_modules/.bin -type f -exec chmod 755 {} \; 2>/dev/null || true
        fi
        
        # DEPOIS: aplicar chmod 644 apenas em arquivos que NÃO são binários
        sudo find frontend/node_modules/ -type f ! -path "*/.bin/*" ! -path "*/bin/*" -exec chmod 644 {} \;
        
        # GARANTIR: aplicar chmod 755 novamente nos binários (para garantir)
        sudo find frontend/node_modules/ -type f -path "*/bin/*" -exec chmod 755 {} \;
        sudo find frontend/node_modules/ -type f -path "*/.bin/*" -exec chmod 755 {} \;
        
        echo "    ✅ node_modules corrigido"
    fi
    
    echo "  ✅ Frontend corrigido!"
    echo ""
else
    echo "  ⚠️  Diretório frontend não encontrado"
    echo ""
fi

echo "=========================================="
echo "✅ Permissões corrigidas com sucesso!"
echo "=========================================="
echo ""
echo "Próximos passos:"
echo "  1. Reinicie os containers:"
echo "     source .env.docker && docker-compose restart"
echo ""
echo "  2. Verifique se pode salvar arquivos sem sudo"
echo ""
