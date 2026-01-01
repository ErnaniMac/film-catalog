<template>
  <div class="films-container">
    <div class="header">
      <h1>Buscar Filmes</h1>
      <p>Encontre seus filmes favoritos usando a API do TMDB</p>
    </div>

    <div class="search-section">
      <div class="search-box">
        <InputText
          v-model="searchInput"
          placeholder="Digite o nome do filme..."
          @keyup.enter="handleSearch"
          class="search-input"
        />
        <Button
          label="Buscar"
          icon="pi pi-search"
          @click="handleSearch"
          :loading="filmStore.loading"
        />
        <Button
          v-if="filmStore.searchQuery"
          label="Limpar"
          icon="pi pi-times"
          severity="secondary"
          @click="handleClear"
        />
      </div>
    </div>

    <div v-if="filmStore.loading" class="loading">
      <ProgressSpinner />
      <p>Buscando filmes...</p>
    </div>

    <div v-else-if="filmStore.movies.length > 0" class="movies-grid">
      <div
        v-for="movie in filmStore.movies"
        :key="movie.id"
        class="movie-card"
      >
        <div class="movie-poster">
          <img
            v-if="movie.poster_path"
            :src="`https://image.tmdb.org/t/p/w500${movie.poster_path}`"
            :alt="movie.title"
          />
          <div v-else class="no-poster">Sem imagem</div>
        </div>
        <div class="movie-info">
          <h3>{{ movie.title }}</h3>
          <p class="release-date">
            {{ formatDate(movie.release_date) }}
          </p>
          <p class="overview">{{ truncateText(movie.overview, 150) }}</p>
          <div class="movie-actions">
            <Button
              v-if="favoriteStore.isFavorite(movie.id)"
              label="Remover dos Favoritos"
              icon="pi pi-heart-fill"
              severity="danger"
              @click="handleRemoveFavorite(movie.id)"
            />
            <Button
              v-else
              label="Adicionar aos Favoritos"
              icon="pi pi-heart"
              @click="handleAddFavorite(movie)"
            />
          </div>
        </div>
      </div>
    </div>

    <div v-else-if="filmStore.searchQuery && !filmStore.loading" class="no-results">
      <p>Nenhum filme encontrado para "{{ filmStore.searchQuery }}"</p>
    </div>

    <div v-if="filmStore.movies.length > 0" class="pagination">
      <Button
        label="Anterior"
        icon="pi pi-chevron-left"
        :disabled="filmStore.currentPage === 1"
        @click="goToPage(filmStore.currentPage - 1)"
      />
      <span class="page-info">
        Página {{ filmStore.currentPage }} de {{ filmStore.totalPages }}
      </span>
      <Button
        label="Próxima"
        icon="pi pi-chevron-right"
        iconPos="right"
        :disabled="filmStore.currentPage >= filmStore.totalPages"
        @click="goToPage(filmStore.currentPage + 1)"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useFilmStore } from '@/stores/film'
import { useFavoriteStore } from '@/stores/favorite'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import ProgressSpinner from 'primevue/progressspinner'
import { useToast } from 'primevue/usetoast'
import dayjs from 'dayjs'

const filmStore = useFilmStore()
const favoriteStore = useFavoriteStore()
const toast = useToast()

const searchInput = ref('')

onMounted(() => {
  favoriteStore.fetchFavorites()
})

async function handleSearch() {
  if (!searchInput.value.trim()) {
    toast.add({
      severity: 'warn',
      summary: 'Atenção',
      detail: 'Digite um termo para buscar',
      life: 3000
    })
    return
  }

  await filmStore.searchMovies(searchInput.value.trim())
}

function handleClear() {
  searchInput.value = ''
  filmStore.clearSearch()
}

async function handleAddFavorite(movie) {
  const result = await favoriteStore.addFavorite(movie)
  if (result.success) {
    toast.add({
      severity: 'success',
      summary: 'Sucesso',
      detail: 'Filme adicionado aos favoritos!',
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

async function handleRemoveFavorite(tmdbId) {
  const favoriteId = favoriteStore.getFavoriteId(tmdbId)
  if (!favoriteId) return

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

function goToPage(page) {
  if (page >= 1 && page <= filmStore.totalPages) {
    filmStore.searchMovies(filmStore.searchQuery, page)
  }
}

function formatDate(dateString) {
  if (!dateString) return 'Data não disponível'
  return dayjs(dateString).format('DD/MM/YYYY')
}

function truncateText(text, maxLength) {
  if (!text) return 'Sem descrição disponível'
  return text.length > maxLength ? text.substring(0, maxLength) + '...' : text
}
</script>

<style scoped>
.films-container {
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

.search-section {
  margin-bottom: 2rem;
}

.search-box {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.search-input {
  flex: 1;
}

.loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem;
  gap: 1rem;
}

.movies-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 2rem;
  margin-bottom: 2rem;
}

.movie-card {
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s, box-shadow 0.2s;
}

.movie-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.movie-poster {
  width: 100%;
  height: 450px;
  overflow: hidden;
  background: #f0f0f0;
}

.movie-poster img {
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

.movie-info {
  padding: 1.5rem;
}

.movie-info h3 {
  margin: 0 0 0.5rem 0;
  font-size: 1.25rem;
  color: #333;
}

.release-date {
  color: #666;
  font-size: 0.9rem;
  margin-bottom: 0.75rem;
}

.overview {
  color: #555;
  line-height: 1.6;
  margin-bottom: 1rem;
}

.movie-actions {
  margin-top: 1rem;
}

.movie-actions button {
  width: 100%;
}

.no-results {
  text-align: center;
  padding: 4rem;
  color: #666;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 2rem;
}

.page-info {
  font-weight: 500;
}
</style>
