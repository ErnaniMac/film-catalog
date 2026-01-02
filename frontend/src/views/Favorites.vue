<template>
  <div class="favorites-container">
    <div class="header">
      <h1>Meus Favoritos</h1>
      <p>Gerencie seus filmes favoritos</p>
    </div>

    <div class="filters">
      <div class="filter-group">
        <label>Filtrar por gênero:</label>
        <Select
          v-model="selectedGenre"
          :options="genreOptions"
          optionLabel="label"
          optionValue="value"
          placeholder="Todos os gêneros"
          @change="handleGenreChange"
          class="genre-select"
        />
      </div>
      <Button
        v-if="selectedGenre"
        label="Limpar Filtro"
        icon="pi pi-times"
        severity="secondary"
        @click="clearFilter"
      />
    </div>

    <div v-if="favoriteStore.loading" class="loading">
      <ProgressSpinner />
      <p>Carregando favoritos...</p>
    </div>

    <div v-else-if="favoriteStore.favorites.length > 0" class="favorites-grid">
      <div
        v-for="favorite in favoriteStore.favorites"
        :key="favorite.id"
        class="favorite-card"
      >
        <div class="favorite-poster">
          <img
            v-if="favorite.poster"
            :src="favorite.poster"
            :alt="favorite.title"
          />
          <div v-else class="no-poster">Sem imagem</div>
        </div>
        <div class="favorite-info">
          <h3>{{ favorite.title }}</h3>
          <div class="overview-container">
            <p 
              class="overview" 
              :class="{ 'truncated': favorite.overview && shouldTruncate(favorite.overview) }"
            >
              {{ truncateText(favorite.overview) }}
            </p>
            <Button
              v-if="favorite.overview && shouldTruncate(favorite.overview)"
              label="Continuar lendo"
              icon="pi pi-eye"
              text
              severity="info"
              class="read-more-btn"
              @click="openSynopsisModal(favorite)"
            />
          </div>
          <div v-if="favorite.genre_ids && favorite.genre_ids.length > 0" class="genres">
            <span
              v-for="genreId in favorite.genre_ids"
              :key="genreId"
              class="genre-badge"
            >
              {{ getGenreName(genreId) }}
            </span>
          </div>
          <div class="favorite-actions">
            <Button
              label="Remover"
              icon="pi pi-trash"
              severity="danger"
              @click="handleRemoveFavorite(favorite.id)"
            />
          </div>
        </div>
      </div>
    </div>

    <div v-else class="no-favorites">
      <p>Você ainda não tem filmes favoritos.</p>
      <Button
        label="Buscar Filmes"
        icon="pi pi-search"
        @click="$router.push('/films')"
      />
    </div>

    <!-- Modal de Sinopse -->
    <Dialog
      v-model:visible="synopsisModalVisible"
      :header="selectedFavorite?.title || 'Sinopse'"
      :modal="true"
      :style="{ width: '600px' }"
    >
      <div class="synopsis-content">
        <p>{{ selectedFavorite?.overview || 'Sem descrição disponível' }}</p>
        <div v-if="selectedFavorite?.genre_ids && selectedFavorite.genre_ids.length > 0" class="modal-genres">
          <strong>Gêneros:</strong>
          <div class="genres-list">
            <span
              v-for="genreId in selectedFavorite.genre_ids"
              :key="genreId"
              class="genre-badge"
            >
              {{ getGenreName(genreId) }}
            </span>
          </div>
        </div>
      </div>
      <template #footer>
        <Button label="Fechar" @click="synopsisModalVisible = false" />
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useFavoriteStore } from '@/stores/favorite'
import Select from 'primevue/select'
import Button from 'primevue/button'
import ProgressSpinner from 'primevue/progressspinner'
import Dialog from 'primevue/dialog'
import { useToast } from 'primevue/usetoast'

const favoriteStore = useFavoriteStore()
const toast = useToast()

const selectedGenre = ref(null)
const synopsisModalVisible = ref(false)
const selectedFavorite = ref(null)

// Gêneros do TMDB (principais)
const genres = {
  28: 'Ação',
  12: 'Aventura',
  16: 'Animação',
  35: 'Comédia',
  80: 'Crime',
  99: 'Documentário',
  18: 'Drama',
  10751: 'Família',
  14: 'Fantasia',
  36: 'História',
  27: 'Terror',
  10402: 'Música',
  9648: 'Mistério',
  10749: 'Romance',
  878: 'Ficção Científica',
  10770: 'TV Movie',
  53: 'Thriller',
  10752: 'Guerra',
  37: 'Faroeste'
}

const genreOptions = [
  { label: 'Todos os gêneros', value: null },
  ...Object.entries(genres).map(([id, name]) => ({
    label: name,
    value: parseInt(id)
  }))
]

onMounted(() => {
  favoriteStore.fetchFavorites()
})

function handleGenreChange() {
  favoriteStore.fetchFavorites(selectedGenre.value)
}

function clearFilter() {
  selectedGenre.value = null
  favoriteStore.fetchFavorites()
}

async function handleRemoveFavorite(favoriteId) {
  const result = await favoriteStore.removeFavorite(favoriteId)
  if (result.success) {
    toast.add({
      severity: 'success',
      summary: 'Sucesso',
      detail: 'Filme removido dos favoritos!',
      life: 3000
    })
  } else {
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: result.error,
      life: 3000
    })
  }
}

function getGenreName(genreId) {
  return genres[genreId] || `Gênero ${genreId}`
}

function shouldTruncate(text) {
  if (!text || text.trim().length === 0) return false
  // Limitar a 72 caracteres exatos
  return text.length > 72
}

function truncateText(text, maxLength = 72) {
  if (!text) return 'Sem descrição'
  if (text.length <= maxLength) return text
  return text.substring(0, maxLength) + '...'
}

function openSynopsisModal(favorite) {
  selectedFavorite.value = favorite
  synopsisModalVisible.value = true
}
</script>

<style scoped>
.favorites-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}

.header {
  text-align: center;
  margin-bottom: 2rem;
}

.header h1 {
  font-size: 2.5rem;
  margin-bottom: 0.5rem;
}

.filters {
  display: flex;
  gap: 1rem;
  align-items: flex-end;
  margin-bottom: 2rem;
  flex-wrap: wrap;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.filter-group label {
  font-weight: 500;
}

.genre-select {
  min-width: 200px;
}

.loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem;
  gap: 1rem;
}

.favorites-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 2rem;
}

@media (max-width: 1400px) {
  .favorites-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 1024px) {
  .favorites-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 640px) {
  .favorites-grid {
    grid-template-columns: 1fr;
  }
}

.favorite-card {
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s, box-shadow 0.2s;
  display: flex;
  flex-direction: column;
  height: 100%;
}

.favorite-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.favorite-poster {
  width: 100%;
  height: 380px;
  overflow: hidden;
  background: #f0f0f0;
  flex-shrink: 0;
}

.favorite-poster img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.no-poster {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #999;
}

.favorite-info {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  flex-grow: 1;
}

.favorite-info h3 {
  margin: 0 0 0.5rem 0;
  font-size: 1.25rem;
  color: #333;
  line-height: 1.3;
  min-height: 2.6rem;
  max-height: 3.9rem;
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
}

.overview-container {
  flex-grow: 1;
  margin-bottom: 1rem;
  min-height: 0;
}

.overview {
  color: #555;
  line-height: 1.3;
  margin-bottom: 0.5rem;
  word-wrap: break-word;
  display: block;
  font-family: 'Courier New', Courier, monospace;
  font-size: 0.9rem;
  white-space: pre-wrap;
}

.overview.truncated {
  /* Limitar a 72 caracteres - já truncado pela função truncateText */
  max-width: 100%;
  overflow: hidden;
}

.read-more-btn {
  padding: 0.25rem 0;
  font-size: 0.9rem;
}

.synopsis-content {
  line-height: 1.8;
  color: #555;
}

.modal-genres {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #e0e0e0;
}

.genres-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-top: 0.5rem;
}

.genres {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.genre-badge {
  background: #e3f2fd;
  color: #1976d2;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.85rem;
}

.favorite-actions {
  margin-top: 1rem;
}

.favorite-actions button {
  width: 100%;
  font-size: 14px;
}

.no-favorites {
  text-align: center;
  padding: 4rem;
  color: #666;
}

.no-favorites p {
  margin-bottom: 1rem;
  font-size: 1.1rem;
}
</style>
