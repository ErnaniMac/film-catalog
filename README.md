# Film Catalog

Projeto full-stack de catálogo de filmes desenvolvido com Laravel (backend) e Vue.js 3 (frontend), integrado com a API do TMDB (The Movie Database).

## 📋 Índice

- [Visão Geral](#visão-geral)
- [Tecnologias](#tecnologias)
- [Pré-requisitos](#pré-requisitos)
- [Instalação](#instalação)
- [Configuração](#configuração)
- [Uso](#uso)
- [Estrutura do Projeto](#estrutura-do-projeto)
- [API Endpoints](#api-endpoints)
- [Testes](#testes)
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

## 🚀 Instalação

### 1. Clone o repositório

```bash
git clone <url-do-repositorio>
cd film-catalog
```

### 2. Configure as variáveis de ambiente

#### Backend

Copie o arquivo `.env.example` para `.env` no diretório `backend/`:

```bash
cp backend/.env.example backend/.env
```

Edite o arquivo `backend/.env` e configure:

```env
APP_NAME="Film Catalog"
APP_ENV=local
APP_KEY=  # Será gerado automaticamente
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

TMDB_API_KEY=sua_chave_api_tmdb
TMDB_API_URL=https://api.themoviedb.org/3
```

**Importante**: Você precisa obter uma chave de API do TMDB em [https://www.themoviedb.org/settings/api](https://www.themoviedb.org/settings/api)

#### Frontend

Copie o arquivo `.env.example` para `.env` no diretório `frontend/`:

```bash
cp frontend/.env.example frontend/.env
```

Edite o arquivo `frontend/.env`:

```env
VITE_APP_NAME="Film Catalog"
VITE_API_URL=http://localhost:8000/api
```

### 3. Configure UID/GID para Docker (Opcional mas recomendado)

**✅ O arquivo `.env.docker` já foi criado automaticamente com seus valores!**

Para evitar problemas de permissão, o arquivo `.env.docker` contém seu UID e GID:
```bash
# Verificar valores configurados
cat .env.docker
# Deve mostrar:
# HOST_UID=1000
# HOST_GID=1000
```

Se precisar atualizar (após mudar de usuário ou sistema):
```bash
echo "HOST_UID=$(id -u)" > .env.docker
echo "HOST_GID=$(id -g)" >> .env.docker
```

### 4. Inicie os containers Docker

```bash
# IMPORTANTE: Carregue o .env.docker antes de iniciar os containers
source .env.docker && docker-compose up -d --build

# Ou exporte manualmente:
export HOST_UID=$(id -u)
export HOST_GID=$(id -g)
docker-compose up -d --build
```

**Nota:** O `docker-compose` não carrega automaticamente o `.env.docker`. Você precisa fazer `source .env.docker` antes de executar comandos do docker-compose.

### 5. Configure o Laravel

```bash
# Entre no container do Laravel
docker-compose exec laravel bash

# Gere a chave da aplicação
php artisan key:generate

# Execute as migrations
php artisan migrate

# Execute os seeders (cria usuário admin e roles)
php artisan db:seed

# Saia do container
exit
```

### 6. Instale as dependências do frontend

```bash
# Entre no container do Node
docker-compose exec node sh

# Instale as dependências
npm install

# Saia do container
exit
```

## ⚙️ Configuração

### Credenciais Padrão (após seed)

- **Email**: admin@example.com
- **Senha**: password

### Configuração do TMDB

1. Acesse [https://www.themoviedb.org/](https://www.themoviedb.org/)
2. Crie uma conta ou faça login
3. Vá em Settings > API
4. Solicite uma API Key
5. Copie a chave e adicione no arquivo `backend/.env` como `TMDB_API_KEY`

## 🎮 Uso

### Iniciar o projeto

```bash
docker-compose up -d
```

### Acessar a aplicação

- **Frontend**: http://localhost:5173
- **Backend API**: http://localhost:8000/api
- **MySQL**: localhost:3307

### Parar o projeto

```bash
docker-compose down
```

### Ver logs

```bash
docker-compose logs -f laravel
docker-compose logs -f node
```

## 📁 Estrutura do Projeto

```
film-catalog/
├── backend/                 # Aplicação Laravel
│   ├── app/
│   │   ├── Http/
│   │   │   └── Controllers/
│   │   │       └── Api/     # Controllers da API
│   │   └── Models/          # Models Eloquent
│   ├── database/
│   │   ├── migrations/      # Migrations do banco
│   │   └── seeders/         # Seeders
│   ├── routes/
│   │   └── api.php          # Rotas da API
│   └── config/              # Arquivos de configuração
│
├── frontend/                # Aplicação Vue 3
│   ├── src/
│   │   ├── components/      # Componentes Vue
│   │   ├── views/           # Views/páginas
│   │   ├── stores/          # Pinia stores
│   │   ├── composables/    # Composables reutilizáveis
│   │   ├── router/          # Configuração do Vue Router
│   │   └── utils/           # Utilitários
│   └── package.json
│
├── docker-compose.yml       # Configuração Docker
└── README.md
```

## 🔌 API Endpoints

### Autenticação
- `POST /api/login` - Login
- `POST /api/logout` - Logout
- `GET /api/user` - Usuário autenticado

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

1. **Verifique o `.env.docker`:**
   ```bash
   cat .env.docker
   id
   ```
   
   Os valores devem corresponder ao seu usuário.

2. **Se necessário, atualize o `.env.docker`:**
   ```bash
   echo "HOST_UID=$(id -u)" > .env.docker
   echo "HOST_GID=$(id -g)" >> .env.docker
   ```

#### Após Corrigir Permissões

Reinicie os containers:

```bash
source .env.docker && docker-compose restart
```

#### Solução Permanente

Para evitar o problema no futuro:

1. Sempre use `source .env.docker` antes de comandos `docker-compose`:
   ```bash
   source .env.docker && docker-compose up -d
   ```

2. O entrypoint dos containers corrige automaticamente as permissões ao iniciar, mas arquivos existentes podem precisar de correção manual uma vez.

3. Se criar novos arquivos manualmente e tiver problemas, execute novamente:
   ```bash
   ./fix-permissions.sh
   ```

2. Reconstrua os containers após configurar as variáveis:
   ```bash
   docker-compose down
   docker-compose build --no-cache
   docker-compose up -d
   ```

3. Verifique que o `docker-compose.yml` está usando `${HOST_UID:-1000}` e `${HOST_GID:-1000}`

## 🧪 Testes

### Backend

```bash
docker-compose exec laravel php artisan test
```

### Frontend

```bash
docker-compose exec node npm run test
```

## 📝 Comandos Úteis

### Backend (Laravel)

```bash
# Entrar no container
docker-compose exec laravel bash

# Executar migrations
php artisan migrate

# Executar seeders
php artisan db:seed

# Criar migration
php artisan make:migration nome_da_migration

# Criar controller
php artisan make:controller NomeController

# Criar model
php artisan make:model NomeModel -m

# Limpar cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

### Frontend (Vue)

```bash
# Entrar no container
docker-compose exec node sh

# Instalar dependências
npm install

# Modo desenvolvimento
npm run dev

# Build produção
npm run build

# Preview build
npm run preview
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

## 📄 Licença

Este projeto é um teste técnico e não possui licença específica.

## 🔗 Links Úteis

- [Laravel Documentation](https://laravel.com/docs)
- [Vue.js Documentation](https://vuejs.org/)
- [TMDB API Documentation](https://developer.themoviedb.org/docs)
- [PrimeVue Documentation](https://primevue.org/)

## 📧 Contato

Para dúvidas ou sugestões, abra uma issue no repositório.
