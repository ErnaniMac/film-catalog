# Cine Catálogo

Projeto full-stack de catálogo de filmes desenvolvido com Laravel (backend) e Vue.js 3 (frontend), integrado com a API do TMDB.

## 🚀 Como Rodar o Projeto Localmente com Docker

### 1. Clone o Repositório

```bash
git clone <url-do-repositorio>
cd film-catalog
```

### 2. Configure as Variáveis de Ambiente

#### Backend

```bash
cp backend/.env.example backend/.env
```

Edite `backend/.env` e configure:

```env
# TMDB API
TMDB_API_KEY=sua_chave_api_tmdb_aqui

# Servidor de e-mail
RESEND_KEY=sua_api_resend_aqui

# Google OAuth
GOOGLE_CLIENT_ID=seu_client_id_aqui
GOOGLE_CLIENT_SECRET=seu_client_secret_aqui
GOOGLE_REDIRECT_URI=http://localhost:8000/api/auth/google/callback
```

#### Frontend

```bash
cp frontend/.env.example frontend/.env
```

**Nota:** Configure `VITE_API_URL` no `.env` para apontar para o backend caso queira utilizar um domínio.

### 3. Inicie os Containers

```bash
docker-compose up -d
```

### 6. Acesse a Aplicação

- **Web**: http://localhost:5173

**Credenciais padrão:**
- Email: `admin@example.com`
- Senha: `password`

## 📍 Onde Está Implementado o CRUD

### Backend

- **Rotas**: `backend/routes/api.php` (linhas 42-43)
- **Controller**: `backend/app/Http/Controllers/Api/FavoriteController.php`
- **Model**: `backend/app/Models/Favorite.php`
- **Migration**: `backend/database/migrations/..._create_favorites_table.php`

**Endpoints:**
- `GET /api/favorites` - Listar favoritos
- `POST /api/favorites` - Adicionar favorito
- `DELETE /api/favorites/{id}` - Remover favorito

### Frontend

- **Store**: `frontend/src/stores/favorite.js`
- **Views**: 
  - `frontend/src/views/Films.vue` - Buscar e adicionar favoritos
  - `frontend/src/views/Favorites.vue` - Listar e remover favoritos
- **Router**: `frontend/src/router/index.js`

### Teste Manual

1. Acesse http://localhost:5173
2. Crie uma conta ou faça login
3. Busque filmes na página `/films`
4. Adicione ou remova filmes dos favoritos clicando no ícone de Estrela
5. Visualize favoritos em `/favorites`

## 🔑 Link para Obter a Chave da API do TMDB

1. Acesse: https://www.themoviedb.org/
2. Crie uma conta ou faça login
3. Vá em **Settings** > **API**
4. Clique em **"Request an API Key"**
5. Selecione **"Developer"**
6. Preencha o formulário:
   - **Application Name**: Cine Catálogo
   - **Application URL**: http://localhost:8000
7. Copie a chave e adicione em `backend/.env`:
   ```env
   TMDB_API_KEY=sua_chave_aqui
   ```

**Links úteis:**
- Site: https://www.themoviedb.org/
- API Settings: https://www.themoviedb.org/settings/api
- Documentação: https://developer.themoviedb.org/docs

## 🛠 Tecnologias

- **Backend**: Laravel 12, Sanctum, MySQL, Redis
- **Frontend**: Vue 3, Vite, Pinia, PrimeVue
- **Infraestrutura**: Docker, Docker Compose
