# Solução para Problemas de Permissão no Frontend

## Problema
Ao tentar salvar arquivos do frontend, aparece erro "insufficient permissions" ou é necessário usar `sudo`.

## Causa
Os arquivos do frontend estão com ownership incorreto no host (ex: `1001:docker` em vez de `1000:1000`).

## Solução Rápida

### 1. Corrigir permissões dos arquivos existentes

Execute o script de correção:

```bash
./fix-frontend-permissions-now.sh
```

Este script vai:
- Corrigir o ownership de todos os arquivos para seu usuário
- Corrigir permissões de diretórios e arquivos
- Corrigir permissões especiais do `node_modules`

**Nota:** Este script requer `sudo` e vai pedir sua senha.

### 2. Reiniciar o container

Após corrigir as permissões, reinicie o container:

```bash
source .env.docker && docker-compose restart node
```

## Como Funciona

1. **Entrypoint do Container**: Quando o container inicia, o entrypoint (rodando como root) corrige as permissões usando `HOST_UID` e `HOST_GID` do ambiente.

2. **Novos Arquivos**: Arquivos criados pelo container terão o ownership correto devido ao `umask 0002` e ao usuário `appuser` com UID/GID correto.

3. **Arquivos Existentes**: Arquivos que já existiam antes da correção precisam ser corrigidos manualmente uma vez com o script.

## Verificação

Verifique se as permissões estão corretas:

```bash
ls -la frontend/ | head -5
```

Os arquivos devem mostrar seu usuário (ex: `ernani-smac`) em vez de `1001 docker`.

## Prevenção

Para evitar o problema no futuro:

1. Sempre use `source .env.docker` antes de comandos `docker-compose`
2. Se criar novos arquivos manualmente, use `chown` se necessário
3. O entrypoint corrige automaticamente ao iniciar o container

## Troubleshooting

Se o problema persistir:

1. Verifique se `.env.docker` está correto:
   ```bash
   cat .env.docker
   id
   ```

2. Pare o container e corrija manualmente:
   ```bash
   docker-compose stop node
   ./fix-frontend-permissions-now.sh
   docker-compose start node
   ```

3. Se necessário, reconstrua o container:
   ```bash
   source .env.docker
   docker-compose stop node
   docker-compose build --no-cache node
   docker-compose up -d node
   ```

