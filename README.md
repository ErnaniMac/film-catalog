# Film Catalog

Projeto full-stack de catálogo de filmes desenvolvido com Laravel (backend) e Vue.js 3 (frontend), integrado com a API do TMDB (The Movie Database).

## 📋 Índice

- [Visão Geral](#visão-geral)
- [Tecnologias](#tecnologias)
- [Pré-requisitos](#pré-requisitos)
- [Docker Pronto para Rodar](#docker-pronto-para-rodar)
- [Como Rodar o Projeto Localmente com Docker](#como-rodar-o-projeto-localmente-com-docker)
- [Como Importar o Banco de Dados](#como-importar-o-banco-de-dados)
- [Onde Está Implementado o CRUD](#onde-está-implementado-o-crud)
- [Como Testar a Aplicação](#como-testar-a-aplicação)
- [Link para Obter a Chave da API do TMDB](#link-para-obter-a-chave-da-api-do-tmdb)
- [Como Subir o Frontend Separado](#como-subir-o-frontend-separado)
- [Estrutura do Projeto](#estrutura-do-projeto)
- [API Endpoints](#api-endpoints)
- [Troubleshooting](#troubleshooting)
- [Contribuindo](#contribuindo)

## 🎯 Visão Geral

Este projeto é um catálogo de filmes que permite:
- Buscar filmes na API do TMDB
- Adicionar/remover filmes aos favoritos
- Gerenciar usuários, roles e permissões (admin)
- Autenticação via Laravel Sanctum
- Interface moderna com Vue 3 e PrimeVue

## 🛠 Tecnologias

### Backend
- **Laravel 12** - Framework PHP
- **Laravel Sanctum** - Autenticação API
- **Spatie Laravel Permission** - Roles e permissões
- **Predis** - Cliente Redis
- **MySQL** - Banco de dados
- **PHPUnit** - Testes

### Frontend
- **Vue 3** - Framework JavaScript
- **Vite** - Build tool
- **Pinia** - Gerenciamento de estado
- **Vue Router** - Roteamento
- **Axios** - Cliente HTTP
- **PrimeVue** - Componentes UI
- **vee-validate + yup** - Validação de formulários
- **dayjs** - Manipulação de datas
- **lodash** - Utilitários

### Infraestrutura
- **Docker Compose** - Containerização
- **MySQL 8.0** - Banco de dados
- **Redis** - Cache e sessões

## 📦 Pré-requisitos

- Docker e Docker Compose instalados
- Git
- (Opcional) PHP 8.1+ e Composer para desenvolvimento local
- (Opcional) Node.js 18+ e npm para desenvolvimento local

## 🐳 Docker Pronto para Rodar

O projeto está completamente configurado para rodar com Docker Compose. Todos os serviços (Laravel, MySQL, Redis, Node.js) estão containerizados e prontos para uso.

**Comando rápido para iniciar:**
```bash
docker-compose up -d
```

Após alguns segundos, a aplicação estará disponível em:
- **Frontend**: http://localhost:5173
- **Backend API**: http://localhost:8000/api

## 🚀 Como Rodar o Projeto Localmente com Docker

Siga este passo a passo completo para configurar e executar o projeto:

### Passo 1: Clone o Repositório

```bash
git clone <url-do-repositorio>
cd film-catalog
```

### Passo 2: Configure as Variáveis de Ambiente

#### Backend (.env)

Copie o arquivo `.env.example` para `.env` no diretório `backend/`:

```bash
cp backend/.env.example backend/.env
```

Edite o arquivo `backend/.env` e configure as seguintes variáveis:

```env
APP_NAME="Film Catalog"
APP_ENV=local
APP_KEY=  # Será gerado no próximo passo
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=film_catalog
DB_USERNAME=film_user
DB_PASSWORD=password

REDIS_HOST=redis
REDIS_PORT=6379

TMDB_API_KEY=sua_chave_api_tmdb_aqui
TMDB_API_URL=https://api.themoviedb.org/3

# Configuração de Email (Resend)
MAIL_MAILER=resend
MAIL_FROM_ADDRESS=noreply@seudominio.com
MAIL_FROM_NAME="${APP_NAME}"
RESEND_KEY=re_sua_chave_resend_aqui
```

**Importante**: Você precisará obter uma chave de API do TMDB. Veja a seção [Link para Obter a Chave da API do TMDB](#link-para-obter-a-chave-da-api-do-tmdb).

#### Frontend (.env)

Copie o arquivo `.env.example` para `.env` no diretório `frontend/`:

```bash
cp frontend/.env.example frontend/.env
```

Edite o arquivo `frontend/.env`:

```env
VITE_APP_NAME="Film Catalog"
VITE_API_URL=http://localhost:8000/api
```

### Passo 3: Configure UID/GID para Docker (Recomendado)

O arquivo `.env` já foi criado automaticamente com seus valores de UID/GID. Verifique se está correto:

```bash
cat .env
# Deve mostrar algo como:
# HOST_UID=1000
# HOST_GID=1000
```

Se precisar atualizar:

```bash
echo "HOST_UID=$(id -u)" > .env
echo "HOST_GID=$(id -g)" >> .env
```

### Passo 4: Inicie os Containers Docker

**IMPORTANTE**: Sempre carregue o `.env` antes de executar comandos do docker-compose:

```bash
# Carregue as variáveis de ambiente
O Docker Compose lê automaticamente o .env

# Inicie os containers
docker-compose up -d --build
```

Este comando irá:
- Construir as imagens Docker (se necessário)
- Criar e iniciar os containers:
  - `film-catalog-mysql` - Banco de dados MySQL
  - `film-catalog-redis` - Cache Redis
  - `film-catalog-laravel` - Backend Laravel
  - `film-catalog-nginx` - Servidor web Nginx
  - `film-catalog-node` - Frontend Vue.js

Aguarde alguns segundos para os containers iniciarem completamente.

### Passo 5: Gere a Chave da Aplicação Laravel

Entre no container do Laravel e gere a chave:

```bash
docker-compose exec laravel php artisan key:generate
```

### Passo 6: Configure o Banco de Dados

Você tem duas opções:

#### Opção A: Usar Migrations e Seeders (Recomendado)

```bash
# Execute as migrations
docker-compose exec laravel php artisan migrate

# Execute os seeders (cria usuário admin e roles)
docker-compose exec laravel php artisan db:seed
```

#### Opção B: Importar Dump SQL

Veja a seção [Como Importar o Banco de Dados](#como-importar-o-banco-de-dados) para instruções detalhadas.

### Passo 7: Verifique se Tudo Está Funcionando

Verifique os logs dos containers:

```bash
# Logs do Laravel
docker-compose logs laravel

# Logs do Frontend
docker-compose logs node

# Logs de todos os serviços
docker-compose logs -f
```

### Passo 8: Acesse a Interface Web

Após todos os passos, acesse:

- **Frontend (Interface Web)**: http://localhost:5173
- **Backend API**: http://localhost:8000/api
- **MySQL**: localhost:3307 (usuário: `film_user`, senha: `password`)

### Credenciais Padrão (após seed)

- **Email**: admin@example.com
- **Senha**: password

## 💾 Como Importar o Banco de Dados

Você tem duas opções para configurar o banco de dados:

### Opção 1: Usar Migrations e Seeders (Recomendado)

Esta é a forma padrão e recomendada:

```bash
# Execute as migrations para criar as tabelas
docker-compose exec laravel php artisan migrate

# Execute os seeders para popular dados iniciais
docker-compose exec laravel php artisan db:seed

# Ou execute ambos de uma vez
docker-compose exec laravel php artisan migrate --seed
```

Os seeders criam:
- Usuário administrador padrão
- Roles e permissões básicas
- Dados de exemplo (se configurados)

### Opção 2: Importar Dump SQL

Se você possui um arquivo `.sql` com dump do banco de dados:

#### Método 1: Via Docker Exec

```bash
# Copie o arquivo SQL para o container MySQL
docker cp seu_dump.sql film-catalog-mysql:/tmp/dump.sql

# Importe o dump
docker-compose exec mysql mysql -u film_user -ppassword film_catalog < /tmp/dump.sql
```

#### Método 2: Via MySQL Client Local

Se você tem o MySQL client instalado localmente:

```bash
# Importe diretamente
mysql -h localhost -P 3307 -u film_user -ppassword film_catalog < seu_dump.sql
```

#### Método 3: Via Container MySQL

```bash
# Entre no container MySQL
docker-compose exec mysql bash

# Dentro do container, importe o dump
mysql -u film_user -ppassword film_catalog < /tmp/dump.sql
```

**Nota**: Se você importar um dump, certifique-se de que:
- O banco de dados `film_catalog` já existe (ou crie manualmente)
- As tabelas não conflitam com migrations existentes
- Os dados estão no formato correto

## 📍 Onde Está Implementado o CRUD

O CRUD completo de filmes favoritos está implementado nos seguintes arquivos e diretórios:

### Backend (Laravel)

#### Rotas da API
**Arquivo**: `backend/routes/api.php`

```php
// Linha 32-33
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('favorites', FavoriteController::class)
        ->only(['index', 'store', 'destroy']);
});
```

**Endpoints disponíveis:**
- `GET /api/favorites` - Listar favoritos do usuário autenticado
- `POST /api/favorites` - Adicionar filme aos favoritos
- `DELETE /api/favorites/{id}` - Remover filme dos favoritos

#### Controller
**Arquivo**: `backend/app/Http/Controllers/Api/FavoriteController.php`

Este controller contém toda a lógica do CRUD:
- `index()` - Lista os favoritos do usuário (com filtro opcional por gênero)
- `store()` - Adiciona um novo filme aos favoritos
- `destroy()` - Remove um filme dos favoritos

#### Model
**Arquivo**: `backend/app/Models/Favorite.php`

Model Eloquent que representa a tabela `favorites`:
- Define campos `fillable`
- Relacionamento `belongsTo` com `User`
- Cast de `genre_ids` para array

#### Migration
**Arquivo**: `backend/database/migrations/2026_01_01_153422_create_favorites_table.php`

Cria a tabela `favorites` no banco de dados com os campos:
- `id` - Chave primária
- `user_id` - Foreign key para usuário
- `tmdb_id` - ID do filme no TMDB
- `title` - Título do filme
- `overview` - Sinopse
- `poster` - URL do poster
- `genre_ids` - IDs dos gêneros (JSON)
- `timestamps` - created_at e updated_at

#### Factory (Para Testes)
**Arquivo**: `backend/database/factories/FavoriteFactory.php`

Factory para criar dados de teste.

### Frontend (Vue.js)

#### Store (Pinia)
**Arquivo**: `frontend/src/stores/favorite.js`

Store Pinia que gerencia o estado dos favoritos:
- `favorites` - Lista de favoritos
- `fetchFavorites()` - Busca favoritos da API
- `addFavorite()` - Adiciona favorito
- `removeFavorite()` - Remove favorito

#### Views/Componentes
**Arquivo**: `frontend/src/views/Favorites.vue`

Componente principal que exibe a lista de favoritos e permite:
- Visualizar filmes favoritos
- Remover filmes dos favoritos
- Filtrar por gênero

**Arquivo**: `frontend/src/views/Films.vue`

Componente que exibe a busca de filmes e permite:
- Buscar filmes na API TMDB
- Adicionar filmes aos favoritos

#### Router
**Arquivo**: `frontend/src/router/index.js`

Define as rotas:
- `/favorites` - Página de favoritos (requer autenticação)

## 🧪 Como Testar a Aplicação

### 1. Testes Automatizados

#### Backend (PHPUnit)

Execute os testes do Laravel:

```bash
docker-compose exec laravel php artisan test
```

Para executar testes específicos:

```bash
# Testar apenas o FavoriteController
docker-compose exec laravel php artisan test --filter FavoriteControllerTest

# Testar com cobertura
docker-compose exec laravel php artisan test --coverage
```

#### Frontend

Execute os testes do Vue (se configurados):

```bash
docker-compose exec node npm run test
```

### 2. Teste Manual da Interface Web

#### Acessar a Aplicação

1. Abra o navegador em: http://localhost:5173
2. Você verá a tela de login

#### Criar uma Conta

1. Clique em "Registrar" ou acesse: http://localhost:5173/register
2. Preencha o formulário de registro
3. Verifique seu email (em desenvolvimento, verifique os logs)
4. Faça login com suas credenciais

#### Testar Funcionalidades

1. **Buscar Filmes**:
   - Na página inicial (`/films`), use a barra de busca
   - Digite o nome de um filme (ex: "The Matrix")
   - Veja os resultados da busca

2. **Adicionar aos Favoritos**:
   - Clique no botão "Adicionar aos Favoritos" em qualquer filme
   - O filme será adicionado à sua lista

3. **Ver Favoritos**:
   - Acesse a página `/favorites`
   - Veja todos os filmes que você favoritou
   - Teste o filtro por gênero (se disponível)

4. **Remover Favoritos**:
   - Na página de favoritos, clique em "Remover dos Favoritos"
   - O filme será removido da lista

### 3. Testar a API Diretamente

#### Usando cURL

```bash
# 1. Fazer login e obter token
TOKEN=$(curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}' \
  | jq -r '.token')

# 2. Listar favoritos
curl -X GET http://localhost:8000/api/favorites \
  -H "Authorization: Bearer $TOKEN"

# 3. Adicionar favorito
curl -X POST http://localhost:8000/api/favorites \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "tmdb_id": 550,
    "title": "Fight Club",
    "overview": "A movie about...",
    "poster": "https://image.tmdb.org/t/p/w500/...",
    "genre_ids": [18, 53]
  }'
```

#### Usando Postman ou Insomnia

1. Importe a coleção de rotas (se disponível)
2. Configure a variável `base_url` como `http://localhost:8000/api`
3. Faça login e copie o token
4. Configure o header `Authorization: Bearer {token}` nas requisições autenticadas
5. Teste os endpoints:
   - `GET /favorites` - Listar favoritos
   - `POST /favorites` - Adicionar favorito
   - `DELETE /favorites/{id}` - Remover favorito

### 4. Verificar Logs

```bash
# Logs do Laravel (backend)
docker-compose logs -f laravel

# Logs do Frontend
docker-compose logs -f node

# Logs do MySQL
docker-compose logs -f mysql

# Logs de todos os serviços
docker-compose logs -f
```

### 5. Dados de Exemplo

Após executar `php artisan db:seed`, você terá:
- **Usuário admin**: admin@example.com / password
- **Roles**: admin, user
- **Permissões**: configuradas automaticamente

## 🔑 Link para Obter a Chave da API do TMDB

### Passo a Passo

1. **Acesse o site do TMDB**:
   - URL: https://www.themoviedb.org/

2. **Crie uma conta ou faça login**:
   - Clique em "Sign Up" ou "Log In" no canto superior direito
   - Se for novo usuário, preencha o formulário de registro
   - Confirme seu email (verifique a caixa de entrada)

3. **Acesse as configurações da API**:
   - Após fazer login, clique no seu avatar/perfil
   - Vá em **Settings** (Configurações)
   - No menu lateral, clique em **API**

4. **Solicite uma API Key**:
   - Clique em **"Request an API Key"** ou **"Create"**
   - Selecione **"Developer"** como tipo de uso
   - Preencha o formulário:
     - **Application Name**: Film Catalog (ou qualquer nome)
     - **Application URL**: http://localhost:8000 (para desenvolvimento)
     - **Application Summary**: Descreva brevemente seu projeto
   - Aceite os termos de uso
   - Clique em **"Submit"**

5. **Copie sua API Key**:
   - Após a aprovação (geralmente instantânea), você verá sua **API Key**
   - Copie a chave (formato: `xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx`)

6. **Configure no projeto**:
   - Abra o arquivo `backend/.env`
   - Adicione ou atualize a linha:
     ```env
     TMDB_API_KEY=sua_chave_api_aqui
     ```
   - Salve o arquivo
   - Reinicie o container Laravel:
     ```bash
     docker-compose restart laravel
     ```

### Links Úteis

- **Site do TMDB**: https://www.themoviedb.org/
- **Página de API**: https://www.themoviedb.org/settings/api
- **Documentação da API**: https://developer.themoviedb.org/docs
- **Status da API**: https://status.themoviedb.org/

### Limites da API

A API do TMDB tem limites de rate:
- **40 requisições por 10 segundos** para cada IP
- O projeto implementa cache para reduzir chamadas à API

## 🎨 Como Subir o Frontend Separado

**Importante**: O frontend **NÃO requer execução separada** pois já está completamente dockerizado e integrado ao `docker-compose.yml`.

Quando você executa `docker-compose up -d`, o frontend Vue.js é automaticamente iniciado no container `film-catalog-node` e fica disponível em http://localhost:5173.

### Por que não precisa rodar separadamente?

O projeto utiliza Docker Compose que gerencia todos os serviços:
- O container `node` já executa `npm run dev` automaticamente
- O Vite está configurado para hot-reload (atualizações automáticas)
- Não é necessário instalar Node.js localmente
- Não é necessário executar `npm install` ou `npm run dev` manualmente

### Se você quiser trabalhar no frontend localmente (opcional)

Caso prefira desenvolver o frontend fora do Docker (não recomendado para este projeto):

```bash
# 1. Entre no diretório do frontend
cd frontend

# 2. Instale as dependências
npm install

# 3. Inicie o servidor de desenvolvimento
npm run dev
```

**Nota**: Se fizer isso, você precisará:
- Ter Node.js 18+ instalado localmente
- Configurar o `VITE_API_URL` no `.env` para apontar para o backend
- Garantir que o backend esteja rodando (via Docker ou localmente)

**Recomendação**: Use o Docker Compose para manter consistência entre desenvolvimento e produção.

## 📁 Estrutura do Projeto

```
film-catalog/
├── backend/                 # Aplicação Laravel
│   ├── app/
│   │   ├── Http/
│   │   │   └── Controllers/
│   │   │       └── Api/     # Controllers da API
│   │   │           ├── FavoriteController.php
│   │   │           ├── AuthController.php
│   │   │           └── ...
│   │   └── Models/          # Models Eloquent
│   │       ├── Favorite.php
│   │       └── User.php
│   ├── database/
│   │   ├── migrations/      # Migrations do banco
│   │   │   └── ..._create_favorites_table.php
│   │   └── seeders/         # Seeders
│   ├── routes/
│   │   └── api.php          # Rotas da API
│   └── config/              # Arquivos de configuração
│
├── frontend/                # Aplicação Vue 3
│   ├── src/
│   │   ├── components/      # Componentes Vue
│   │   ├── views/           # Views/páginas
│   │   │   ├── Films.vue
│   │   │   └── Favorites.vue
│   │   ├── stores/          # Pinia stores
│   │   │   └── favorite.js
│   │   ├── router/          # Configuração do Vue Router
│   │   └── utils/           # Utilitários
│   └── package.json
│
├── docker-compose.yml       # Configuração Docker
├── .env                     # Configurações do Docker (portas, MySQL, UID/GID)
├── fix-permissions.sh       # Script de correção de permissões
└── README.md
```

## 🔌 API Endpoints

### Autenticação
- `POST /api/login` - Login
- `POST /api/logout` - Logout
- `GET /api/user` - Usuário autenticado
- `POST /api/register` - Registrar novo usuário
- `POST /api/forgot-password` - Solicitar reset de senha
- `POST /api/reset-password` - Resetar senha

### Filmes (TMDB)
- `GET /api/tmdb/search?query={query}&page={page}` - Buscar filmes

### Favoritos (requer autenticação)
- `GET /api/favorites?genre_id={id}` - Listar favoritos (filtro opcional por gênero)
- `POST /api/favorites` - Adicionar favorito
- `DELETE /api/favorites/{id}` - Remover favorito

### Admin (requer autenticação e role admin)
- `GET /api/users` - Listar usuários
- `POST /api/users` - Criar usuário
- `GET /api/users/{id}` - Mostrar usuário
- `PUT /api/users/{id}` - Atualizar usuário
- `DELETE /api/users/{id}` - Deletar usuário

- `GET /api/roles` - Listar roles
- `POST /api/roles` - Criar role
- `GET /api/roles/{id}` - Mostrar role
- `PUT /api/roles/{id}` - Atualizar role
- `DELETE /api/roles/{id}` - Deletar role

- `GET /api/permissions` - Listar permissões
- `POST /api/permissions` - Criar permissão
- `GET /api/permissions/{id}` - Mostrar permissão
- `PUT /api/permissions/{id}` - Atualizar permissão
- `DELETE /api/permissions/{id}` - Deletar permissão

## 🔧 Troubleshooting

### Problemas de Permissão

Se você encontrar erros como "Failed to save ... insufficient permissions" ou precisar usar `sudo` para salvar arquivos:

#### Solução Rápida

Execute o script de correção de permissões:

```bash
./fix-permissions.sh
```

Este script corrige automaticamente:
- ✅ Ownership de todos os arquivos (backend e frontend) para seu usuário
- ✅ Permissões corretas para diretórios e arquivos
- ✅ Permissões especiais para `storage` e `bootstrap/cache` do Laravel (775/664)
- ✅ Permissões especiais para `node_modules` (binários executáveis)

**Nota:** O script requer `sudo` e vai pedir sua senha.

#### Verificação

1. **Verifique o `.env`:**
   ```bash
   cat .env
   id
   ```
   
   Os valores devem corresponder ao seu usuário.

2. **Se necessário, atualize o `.env`:**
   ```bash
   echo "HOST_UID=$(id -u)" > .env
   echo "HOST_GID=$(id -g)" >> .env
   ```

#### Após Corrigir Permissões

Reinicie os containers:

```bash
docker-compose restart
```

#### Solução Permanente

Para evitar o problema no futuro:

1. O Docker Compose lê automaticamente o arquivo `.env` na raiz:
   ```bash
   docker-compose up -d
   ```

2. O entrypoint dos containers corrige automaticamente as permissões ao iniciar, mas arquivos existentes podem precisar de correção manual uma vez.

3. Se criar novos arquivos manualmente e tiver problemas, execute novamente:
   ```bash
   ./fix-permissions.sh
   ```

### Outros Problemas Comuns

#### Container não inicia

```bash
# Verifique os logs
docker-compose logs [nome-do-container]

# Reconstrua os containers
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

#### Erro de conexão com banco de dados

```bash
# Verifique se o MySQL está rodando
docker-compose ps mysql

# Verifique as variáveis de ambiente
docker-compose exec laravel env | grep DB_
```

#### Erro 500 no backend

```bash
# Limpe o cache
docker-compose exec laravel php artisan cache:clear
docker-compose exec laravel php artisan config:clear
docker-compose exec laravel php artisan route:clear
```

## 📝 Comandos Úteis

### Backend (Laravel)

```bash
# Entrar no container
docker-compose exec laravel bash

# Executar migrations
docker-compose exec laravel php artisan migrate

# Executar seeders
docker-compose exec laravel php artisan db:seed

# Criar migration
docker-compose exec laravel php artisan make:migration nome_da_migration

# Criar controller
docker-compose exec laravel php artisan make:controller NomeController

# Criar model
docker-compose exec laravel php artisan make:model NomeModel -m

# Limpar cache
docker-compose exec laravel php artisan cache:clear
docker-compose exec laravel php artisan config:clear
docker-compose exec laravel php artisan route:clear
```

### Frontend (Vue)

```bash
# Entrar no container
docker-compose exec node sh

# Instalar dependências (geralmente não necessário, já instalado)
docker-compose exec node npm install

# Ver logs do frontend
docker-compose logs -f node
```

### Docker

```bash
# Parar todos os containers
docker-compose down

# Parar e remover volumes
docker-compose down -v

# Reconstruir containers
docker-compose build --no-cache

# Ver status dos containers
docker-compose ps

# Ver logs
docker-compose logs -f [serviço]
```

## 🤝 Contribuindo

1. Faça um fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'feat: adiciona AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

### Padrão de Commits

Use [Conventional Commits](https://www.conventionalcommits.org/):

- `feat:` - Nova funcionalidade
- `fix:` - Correção de bug
- `docs:` - Documentação
- `style:` - Formatação
- `refactor:` - Refatoração
- `test:` - Testes
- `chore:` - Tarefas de build/configuração

**Importante**: Mensagens de commit devem ter no máximo 120 caracteres na linha principal.

## 📄 Licença

Este projeto é um teste técnico e não possui licença específica.

## 🔗 Links Úteis

- [Laravel Documentation](https://laravel.com/docs)
- [Vue.js Documentation](https://vuejs.org/)
- [TMDB API Documentation](https://developer.themoviedb.org/docs)
- [PrimeVue Documentation](https://primevue.org/)
- [Docker Documentation](https://docs.docker.com/)

## 📧 Contato

Para dúvidas ou sugestões, abra uma issue no repositório.
