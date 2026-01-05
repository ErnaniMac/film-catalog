<template>
  <div class="films-container">
    <div class="header">
      <h1>Meus Favoritos</h1>
    </div>

    <div class="filters-section">
      <div class="filter-group">
        <label>Filtrar por gênero:</label>
        <Dropdown
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

    <TransitionGroup v-else-if="favoriteStore.favorites.length > 0" name="fade" tag="div" class="movies-grid">
      <div
        v-for="favorite in favoriteStore.favorites"
        :key="favorite.id"
        class="movie-card"
        @mouseenter="handleMouseEnter(favorite)"
        @mouseleave="handleMouseLeave(favorite.id)"
      >
        <div class="movie-poster">
          <img
            v-if="favorite.poster"
            :src="favorite.poster"
            :alt="favorite.title"
          />
          <div v-else class="no-poster">Sem imagem</div>
          
          <!-- Estrela de favorito e nota no canto superior esquerdo -->
          <div class="favorite-rating-container">
            <button
              class="favorite-star is-favorite"
              @click.stop="handleRemoveFavorite(favorite.id)"
              title="Remover dos favoritos"
            >
              <i class="pi pi-star-fill"></i>
            </button>
            <span class="movie-rating" v-if="movieDetails[favorite.tmdb_id]">
              {{ movieDetails[favorite.tmdb_id].vote_average ? movieDetails[favorite.tmdb_id].vote_average.toFixed(1) : 'N/A' }}
              <span class="movie-vote-count" v-if="movieDetails[favorite.tmdb_id].vote_count">
                ({{ formatVoteCount(movieDetails[favorite.tmdb_id].vote_count) }})
              </span>
            </span>
          </div>
          
          <!-- Ícones de controle no topo -->
          <div 
            v-show="hoveredMovie === favorite.id"
            class="card-controls"
          >
            <button
              class="control-icon"
              :class="{ active: clickedView[favorite.id] === 'synopsis' || (!clickedView[favorite.id] && hoveredMovie === favorite.id) }"
              @click.stop="toggleActiveView(favorite.id, 'synopsis')"
              title="Sinopse"
            >
              <i class="pi pi-file-edit"></i>
            </button>
            <button
              class="control-icon"
              :class="{ active: clickedView[favorite.id] === 'cast' }"
              @click.stop="toggleActiveView(favorite.id, 'cast')"
              title="Personagens"
            >
              <i class="pi pi-users"></i>
            </button>
            <button
              class="control-icon"
              :class="{ active: clickedView[favorite.id] === 'info' }"
              @click.stop="toggleActiveView(favorite.id, 'info')"
              title="Informações"
            >
              <i class="pi pi-info-circle"></i>
            </button>
          </div>

          <!-- Overlay com conteúdo (sinopse, cast ou info) -->
          <div
            class="card-overlay"
            :class="{
              'show-synopsis': activeView[favorite.id] === 'synopsis',
              'show-cast': activeView[favorite.id] === 'cast',
              'show-info': activeView[favorite.id] === 'info',
              'visible': activeView[favorite.id] !== null && activeView[favorite.id] !== undefined
            }"
          >
            <!-- Sinopse (padrão ao passar mouse) -->
            <div v-if="activeView[favorite.id] === 'synopsis'" class="overlay-content synopsis-content">
              <h4>{{ favorite.title }}</h4>
              <p>{{ favorite.overview || 'Sem descrição disponível' }}</p>
            </div>

            <!-- Elenco (6 principais atores) -->
            <div v-if="activeView[favorite.id] === 'cast'" class="overlay-content cast-content">
              <h4>Elenco Principal</h4>
              <div v-if="loadingCast[favorite.id]" class="loading-cast">
                <ProgressSpinner />
                <p>Carregando...</p>
              </div>
              <div v-else-if="movieCast[favorite.id] && movieCast[favorite.id].length > 0" class="cast-grid">
                <div
                  v-for="actor in movieCast[favorite.id].slice(0, 9)"
                  :key="actor.id"
                  class="cast-member"
                >
                  <img
                    v-if="actor.profile_path"
                    :src="`https://image.tmdb.org/t/p/w185${actor.profile_path}`"
                    :alt="actor.name"
                    class="actor-photo"
                  />
                  <div v-else class="actor-photo no-photo">
                    <i class="pi pi-user"></i>
                  </div>
                  <p class="actor-name">{{ actor.name }}</p>
                  <p class="actor-character">{{ actor.character }}</p>
                </div>
              </div>
              <div v-else class="no-cast">
                <p>Elenco não disponível</p>
              </div>
            </div>

            <!-- Informações adicionais -->
            <div v-if="activeView[favorite.id] === 'info'" class="overlay-content info-content">
              <h4>Informações</h4>
              <div v-if="loadingInfo[favorite.id]" class="loading-info">
                <ProgressSpinner />
                <p>Carregando...</p>
              </div>
              <div v-else-if="movieInfo[favorite.id]" class="info-list">
                <div class="info-item">
                  <strong>Ano de Lançamento:</strong>
                  <span>{{ getYear(movieInfo[favorite.id].release_date) }}</span>
                </div>
                <div class="info-item">
                  <strong>Gêneros:</strong>
                  <span>{{ getGenres(movieInfo[favorite.id].genres) }}</span>
                </div>
                <div v-if="getDirector(movieInfo[favorite.id])" class="info-item">
                  <strong>Direção:</strong>
                  <span>{{ getDirector(movieInfo[favorite.id]) }}</span>
                </div>
                <div v-if="movieInfo[favorite.id].certification || movieInfo[favorite.id].adult !== undefined" class="info-item">
                  <strong>Classificação:</strong>
                  <span>{{ movieInfo[favorite.id].adult ? '18+' : 'Livre' }}</span>
                </div>
                <div v-if="movieInfo[favorite.id].runtime" class="info-item">
                  <strong>Duração:</strong>
                  <span>{{ movieInfo[favorite.id].runtime }} minutos</span>
                </div>
                <div v-if="movieInfo[favorite.id].vote_average" class="info-item">
                  <strong>Avaliação:</strong>
                  <span>{{ movieInfo[favorite.id].vote_average.toFixed(1) }}/10</span>
                </div>
              </div>
              <div v-else class="no-info">
                <p>Informações não disponíveis</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </TransitionGroup>

    <div v-else class="no-results">
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
import { ref, onMounted, computed } from 'vue'
import { useFavoriteStore } from '@/stores/favorite'
import { useFilmStore } from '@/stores/film'
import Button from 'primevue/button'
import ProgressSpinner from 'primevue/progressspinner'
import Dropdown from 'primevue/dropdown'
import { useToast } from 'primevue/usetoast'
import { useRouter } from 'vue-router'
import dayjs from 'dayjs'

const favoriteStore = useFavoriteStore()
const filmStore = useFilmStore()
const toast = useToast()
const router = useRouter()

const hoveredMovie = ref(null)
const activeView = ref({}) // 'synopsis', 'cast', 'info'
const clickedView = ref({}) // Para manter view ativa mesmo sem hover
const movieCast = ref({})
const movieInfo = ref({})
const movieDetails = ref({})
const loadingCast = ref({})
const loadingInfo = ref({})
const selectedGenre = ref(null)

// Opções de gêneros
const genreOptions = computed(() => {
  const options = [{ label: 'Todos os gêneros', value: null }]
  if (filmStore.genres.length > 0) {
    options.push(...filmStore.genres.map(genre => ({
      label: genre.name,
      value: genre.id
    })))
  }
  return options
})

onMounted(async () => {
  await filmStore.fetchGenres()
  selectedGenre.value = favoriteStore.selectedGenre
  await favoriteStore.fetchFavorites(selectedGenre.value)
  // Carregar detalhes básicos de todos os favoritos para ter rating
  for (const favorite of favoriteStore.favorites) {
    if (!movieDetails.value[favorite.tmdb_id]) {
      try {
        const details = await filmStore.getMovieDetails(favorite.tmdb_id)
        if (details) {
          movieDetails.value[favorite.tmdb_id] = details
        }
      } catch (error) {
        console.error('Erro ao carregar detalhes do filme:', error)
      }
    }
  }
})

function handleGenreChange() {
  favoriteStore.fetchFavorites(selectedGenre.value)
}

function clearFilter() {
  selectedGenre.value = null
  favoriteStore.fetchFavorites(null)
}

function handleMouseEnter(favorite) {
  hoveredMovie.value = favorite.id
  // Se há uma view clicada, usar ela; senão, mostrar sinopse por padrão
  if (clickedView.value[favorite.id]) {
    activeView.value[favorite.id] = clickedView.value[favorite.id]
  } else {
    // Mostrar sinopse por padrão ao passar o mouse
    activeView.value[favorite.id] = 'synopsis'
  }
}

function handleMouseLeave(favoriteId) {
  hoveredMovie.value = null
  // Sempre limpar a view ativa quando o mouse sair, mesmo se houver view clicada
  // A view clicada será restaurada quando o mouse voltar
  activeView.value[favoriteId] = null
}

function setActiveView(favoriteId, view) {
  // Marcar como view clicada para manter ativa mesmo sem hover
  clickedView.value[favoriteId] = view
  activeView.value[favoriteId] = view
  
  const favorite = favoriteStore.favorites.find(f => f.id === favoriteId)
  if (!favorite) return
  
  if (view === 'cast' && !movieCast.value[favoriteId]) {
    loadCast(favorite.tmdb_id, favoriteId)
  } else if (view === 'info' && !movieInfo.value[favoriteId]) {
    loadInfo(favorite.tmdb_id, favoriteId)
  } else if (view === 'synopsis') {
    // Sinopse já está disponível
  }
}

// Função para fechar a view ativa (pode ser chamada ao clicar novamente no mesmo ícone)
function toggleActiveView(favoriteId, view) {
  if (clickedView.value[favoriteId] === view) {
    // Se já está ativa, desativar
    clickedView.value[favoriteId] = null
    activeView.value[favoriteId] = null
  } else {
    // Ativar a view
    setActiveView(favoriteId, view)
  }
}

async function loadCast(tmdbId, favoriteId) {
  if (loadingCast.value[favoriteId]) return
  
  loadingCast.value[favoriteId] = true
  try {
    const details = await filmStore.getMovieDetails(tmdbId)
    if (details && details.credits && details.credits.cast) {
      movieCast.value[favoriteId] = details.credits.cast
    } else {
      movieCast.value[favoriteId] = []
    }
  } catch (error) {
    console.error('Erro ao carregar elenco:', error)
    movieCast.value[favoriteId] = []
  } finally {
    loadingCast.value[favoriteId] = false
  }
}

async function loadInfo(tmdbId, favoriteId) {
  if (loadingInfo.value[favoriteId]) return
  
  loadingInfo.value[favoriteId] = true
  try {
    const details = await filmStore.getMovieDetails(tmdbId)
    if (details) {
      movieInfo.value[favoriteId] = details
      movieDetails.value[tmdbId] = details
    } else {
      movieInfo.value[favoriteId] = null
    }
  } catch (error) {
    console.error('Erro ao carregar informações:', error)
    movieInfo.value[favoriteId] = null
  } finally {
    loadingInfo.value[favoriteId] = false
  }
}

async function handleRemoveFavorite(favoriteId) {
  // Guardar tmdb_id antes de remover
  const favorite = favoriteStore.favorites.find(f => f.id === favoriteId)
  const tmdbId = favorite?.tmdb_id
  
  const result = await favoriteStore.removeFavorite(favoriteId)
  if (result.success) {
    // Limpar dados do filme removido após a animação
    setTimeout(() => {
      delete movieCast.value[favoriteId]
      delete movieInfo.value[favoriteId]
      delete activeView.value[favoriteId]
      delete clickedView.value[favoriteId]
      // Limpar movieDetails se não houver mais favoritos com esse tmdb_id
      if (tmdbId) {
        const hasOtherFavorite = favoriteStore.favorites.some(f => f.tmdb_id === tmdbId)
        if (!hasOtherFavorite) {
          delete movieDetails.value[tmdbId]
        }
      }
    }, 350)
    
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
      detail: result.error || 'Erro ao remover dos favoritos',
      life: 3000
    })
  }
}

function getYear(dateString) {
  if (!dateString) return 'Não disponível'
  return dayjs(dateString).format('YYYY')
}

function getGenres(genres) {
  if (!genres || !Array.isArray(genres)) return 'Não disponível'
  return genres.map(g => g.name).join(', ')
}

function getDirector(movieInfo) {
  if (!movieInfo || !movieInfo.credits || !movieInfo.credits.crew) return null
  
  const directors = movieInfo.credits.crew.filter(
    person => person.job === 'Director'
  )
  
  if (directors.length === 0) return null
  
  return directors.map(d => d.name).join(', ')
}

function formatVoteCount(count) {
  if (count >= 1000000) {
    return (count / 1000000).toFixed(1) + 'M'
  } else if (count >= 1000) {
    return (count / 1000).toFixed(1) + 'K'
  }
  return count.toString()
}
</script>

<style scoped>
.films-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem;
}

.header {
  text-align: center;
  margin-bottom: 2rem;
}

.header h1 {
  font-size: 1.8rem;
  margin-bottom: 0.5rem;
  text-transform: uppercase;
}

.filters-section {
  margin-bottom: 2rem;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.filter-group {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.filter-group label {
  font-size: 0.95rem;
  font-weight: 500;
  color: #475569;
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
  grid-template-columns: repeat(3, 1fr);
  gap: 2rem;
  margin-bottom: 2rem;
}

/* Transições para remoção suave */
.fade-enter-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.fade-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.fade-enter-from {
  opacity: 0;
  transform: scale(0.9);
}

.fade-leave-to {
  opacity: 0;
  transform: scale(0.9) translateY(-20px);
}

.fade-enter-to,
.fade-leave-from {
  opacity: 1;
  transform: scale(1);
}

@media (max-width: 1024px) {
  .movies-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 640px) {
  .movies-grid {
    grid-template-columns: 1fr;
  }
}

.movie-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
  border: 1px solid #e9ecef;
  transition: transform 0.4s ease, box-shadow 0.4s ease, border-color 0.4s ease;
  display: flex;
  flex-direction: column;
  height: 100%;
}

.movie-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.15);
  border-color: #c7d2fe;
}

.movie-poster {
  width: 100%;
  height: 620px;
  overflow: hidden;
  background: #f1f5f9;
  flex-shrink: 0;
  position: relative;
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
  color: #94a3b8;
  background: #f1f5f9;
}

/* Controles no topo da imagem */
.card-controls {
  position: absolute;
  top: 10px;
  right: 10px;
  display: flex;
  gap: 8px;
  z-index: 10;
  opacity: 0;
  transition: opacity 0.8s ease-out;
}

.movie-poster:hover .card-controls {
  opacity: 1;
}

.control-icon {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.95);
  border: 2px solid rgba(99, 102, 241, 0.3);
  color: #6366f1;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
  backdrop-filter: blur(4px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.control-icon:hover {
  background: rgba(255, 255, 255, 1);
  border-color: rgba(99, 102, 241, 0.6);
  transform: scale(1.1);
}

.control-icon.active {
  background: rgba(99, 102, 241, 0.95);
  border-color: rgba(99, 102, 241, 0.8);
  color: white;
}

.control-icon i {
  font-size: 16px;
}

/* Container da estrela e nota */
.favorite-rating-container {
  position: absolute;
  top: 10px;
  left: 10px;
  display: flex;
  align-items: center;
  gap: 8px;
  z-index: 20;
  background: rgba(0, 0, 0, 0.4);
  padding: 6px 12px;
  border-radius: 20px;
  backdrop-filter: blur(4px);
}

/* Estrela de favorito no canto superior esquerdo */
.favorite-star {
  width: auto;
  height: auto;
  border-radius: 0;
  border: none;
  background: none;
  padding: 0;
  color: #ffc107;
  box-shadow: none;
  backdrop-filter: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  animation: starPulse 2s ease-in-out infinite;
}

.movie-rating {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  color: #ffffff;
  font-size: 1rem;
  font-weight: 600;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
}

.movie-vote-count {
  font-size: 0.85rem;
  opacity: 0.85;
  font-weight: 400;
}

@keyframes starPulse {
  0%, 100% {
    transform: translate(0, 0) scale(1) rotate(0deg);
  }
  25% {
    transform: translate(2px, -3px) scale(1.05) rotate(3deg);
  }
  50% {
    transform: translate(-2px, -2px) scale(1.1) rotate(-3deg);
  }
  75% {
    transform: translate(1px, -4px) scale(1.05) rotate(2deg);
  }
}

.favorite-star i {
  font-size: 32px;
  filter: drop-shadow(0 2px 8px rgba(255, 193, 7, 0.9));
}

.favorite-star:hover {
  background: none;
  color: #ffb300;
  transform: scale(1.25);
  animation: none;
}

/* Overlay com conteúdo */
.card-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(30, 41, 59, 0);
  backdrop-filter: blur(0px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  opacity: 0;
  visibility: hidden;
  transition: opacity 0.4s ease-out, 
              background 0.5s ease-out, 
              backdrop-filter 0.5s ease-out,
              transform 0.4s ease-out,
              visibility 0s linear 0s;
  transform: translateY(15px);
  z-index: 5;
  pointer-events: none;
}

.card-overlay.visible {
  opacity: 1;
  visibility: visible;
  background: rgba(30, 41, 59, 0.95);
  backdrop-filter: blur(4px);
  padding-top: 80px;
  transform: translateY(0);
  pointer-events: auto;
  transition: opacity 0.4s ease-out, 
              background 0.5s ease-out, 
              backdrop-filter 0.5s ease-out,
              transform 0.4s ease-out,
              visibility 0s linear 0s;
}

.overlay-content {
  width: 100%;
  height: 100%;
  color: #1e293b;
  overflow-y: auto;
}

.overlay-content h4 {
  margin: 0 0 12px 0;
  font-size: 1.2rem;
  color: #ffffff;
  border-bottom: 2px solid rgba(255, 255, 255, 0.2);
  padding-bottom: 8px;
}

.overlay-content p {
  line-height: 1.6;
  margin: 0;
  color: #e2e8f0;
}

/* Sinopse */
.synopsis-content {
  display: flex;
  flex-direction: column;
}

.synopsis-content p {
  flex: 1;
  font-size: 0.95rem;
}

/* Elenco */
.cast-content {
  display: flex;
  flex-direction: column;
}

.cast-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
  row-gap: 24px;
  margin-top: 12px;
}

.cast-member {
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  position: relative;
  z-index: 1;
}

.actor-photo {
  width: 80px;
  height: 80px;
  border-radius: 4px;
  object-fit: cover;
  margin: 0 auto 8px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  display: block;
  position: relative;
  z-index: 1;
}

.actor-photo.no-photo {
  background: #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #94a3b8;
}

.actor-photo.no-photo i {
  font-size: 32px;
}

.actor-name {
  font-size: 0.85rem;
  font-weight: 600;
  margin: 4px 0 2px;
  color: #ffffff;
  position: relative;
  z-index: 2;
}

.actor-character {
  font-size: 0.75rem;
  color: #cbd5e1;
  margin: 0;
  position: relative;
  z-index: 2;
}

.loading-cast,
.loading-info {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  padding: 2rem;
}

.no-cast,
.no-info {
  text-align: center;
  padding: 2rem;
  color: #cbd5e1;
}

/* Informações */
.info-content {
  display: flex;
  flex-direction: column;
}

.info-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-top: 12px;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
  padding-bottom: 12px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

.info-item:last-child {
  border-bottom: none;
}

.info-item strong {
  color: #ffffff;
  font-size: 0.9rem;
}

.info-item span {
  color: #64748b;
  font-size: 0.95rem;
}

.no-results {
  text-align: center;
  padding: 4rem;
  color: #666;
}

/* Scrollbar personalizada para overlay */
.overlay-content::-webkit-scrollbar {
  width: 6px;
}

.overlay-content::-webkit-scrollbar-track {
  background: rgba(241, 245, 249, 0.5);
}

.overlay-content::-webkit-scrollbar-thumb {
  background: rgba(99, 102, 241, 0.3);
  border-radius: 3px;
}

.overlay-content::-webkit-scrollbar-thumb:hover {
  background: rgba(99, 102, 241, 0.5);
}
</style>
