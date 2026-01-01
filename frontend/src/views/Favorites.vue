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
          <p class="overview">{{ favorite.overview || 'Sem descrição' }}</p>
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
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useFavoriteStore } from '@/stores/favorite'
import Select from 'primevue/select'
import Button from 'primevue/button'
import ProgressSpinner from 'primevue/progressspinner'
import { useToast } from 'primevue/usetoast'

const favoriteStore = useFavoriteStore()
const toast = useToast()

const selectedGenre = ref(null)

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
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 2rem;
}

.favorite-card {
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s, box-shadow 0.2s;
}

.favorite-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.favorite-poster {
  width: 100%;
  height: 450px;
  overflow: hidden;
  background: #f0f0f0;
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
}

.favorite-info h3 {
  margin: 0 0 0.5rem 0;
  font-size: 1.25rem;
  color: #333;
}

.overview {
  color: #555;
  line-height: 1.6;
  margin-bottom: 1rem;
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
