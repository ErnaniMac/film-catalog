<template>
  <div class="films-container">
    <div class="header">
      <h1>Encontre seus filmes favoritos</h1>
    </div>

    <div class="search-section">
      <div class="search-container">
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
        <div class="filters-button-section">
          <Button
            label="Filtros"
            icon="pi pi-filter"
            @click="filtersModalVisible = true"
            severity="secondary"
          />
          <Button
            label="Ordenação"
            icon="pi pi-sort"
            @click="sortModalVisible = true"
            severity="secondary"
          />
        </div>
      </div>
    </div>

    <!-- Modal de Filtros -->
    <Dialog
      v-model:visible="filtersModalVisible"
      header="Filtros"
      :modal="true"
      :style="{ width: '600px' }"
      :autoFocus="false"
      :closable="true"
      :dismissableMask="true"
      class="filters-modal"
    >
      <div class="filters-section">
        <div class="filters-grid">
          <div class="filter-item">
            <label for="genre-filter">Gênero</label>
            <Select
              id="genre-filter"
              v-model="selectedGenre"
              :options="genreOptions"
              optionLabel="label"
              optionValue="value"
              placeholder="Selecione"
              class="filter-dropdown"
              @change="handleFilterChange"
            />
          </div>

          <div class="filter-item">
            <label for="year-filter">Ano</label>
            <Select
              id="year-filter"
              v-model="selectedYear"
              :options="yearDropdownOptions"
              optionLabel="label"
              optionValue="value"
              placeholder="Selecione"
              class="filter-dropdown"
              @change="handleFilterChange"
            />
          </div>

        </div>
      </div>
      <template #footer>
        <Button
          label="Limpar"
          severity="secondary"
          outlined
          @click="handleClearFilters"
          class="clear-button"
        />
        <Button
          label="Filtrar"
          @click="handleApplyFilters"
          :loading="filmStore.loading"
          class="filter-button"
        />
      </template>
    </Dialog>

    <!-- Modal de Ordenação -->
    <Dialog
      v-model:visible="sortModalVisible"
      header="Ordenação"
      :modal="true"
      :style="{ width: '500px' }"
      :autoFocus="false"
      :closable="true"
      :dismissableMask="true"
      class="sort-modal"
    >
      <div class="sort-section">
        <div class="sort-options">
          <div
            v-for="option in sortOptions"
            :key="option.value"
            class="sort-option"
            @click="selectedSort = option.value"
          >
            <input
              type="radio"
              :id="`sort-${option.value}`"
              :value="option.value"
              v-model="selectedSort"
              class="sort-radio"
            />
            <label :for="`sort-${option.value}`" class="sort-label">
              <span class="sort-icon">{{ option.icon }}</span>
              <span class="sort-text">{{ option.label }}</span>
            </label>
          </div>
        </div>
      </div>
      <template #footer>
        <Button
          label="Cancelar"
          severity="secondary"
          outlined
          @click="sortModalVisible = false"
          class="cancel-button"
        />
        <Button
          label="Aplicar"
          @click="handleApplySort"
          class="apply-button"
        />
      </template>
    </Dialog>

    <div v-if="filmStore.loading" class="loading">
      <ProgressSpinner />
      <p>Buscando filmes...</p>
    </div>

    <TransitionGroup v-else-if="filmStore.movies.length > 0" name="fade" tag="div" class="movies-grid">
      <div
        v-for="movie in displayedMovies"
        :key="movie.id"
        class="movie-card"
        @mouseenter="handleMouseEnter(movie)"
        @mouseleave="handleMouseLeave(movie.id)"
      >
        <div class="movie-poster">
          <img
            v-if="movie.poster_path"
            :src="`https://image.tmdb.org/t/p/w500${movie.poster_path}`"
            :alt="movie.title"
          />
          <div v-else class="no-poster">Sem imagem</div>
          
          <!-- Estrela de favorito e nota no canto superior esquerdo -->
          <div class="favorite-rating-container">
            <button
              class="favorite-star"
              :class="{ 
                'is-favorite': favoriteStore.isFavorite(movie.id)
              }"
              @click.stop="toggleFavorite(movie)"
              :title="favoriteStore.isFavorite(movie.id) ? 'Remover dos favoritos' : 'Adicionar aos favoritos'"
            >
              <i :class="favoriteStore.isFavorite(movie.id) ? 'pi pi-star-fill' : 'pi pi-star'"></i>
            </button>
            <span class="movie-rating">
              {{ movie.vote_average ? movie.vote_average.toFixed(1) : 'N/A' }}
              <span class="movie-vote-count" v-if="movie.vote_count">
                ({{ formatVoteCount(movie.vote_count) }})
              </span>
            </span>
          </div>
          
          <!-- Ícones de controle no topo -->
          <div 
            v-show="hoveredMovie === movie.id"
            class="card-controls"
          >
            <button
              class="control-icon"
              :class="{ active: clickedView[movie.id] === 'synopsis' || (!clickedView[movie.id] && hoveredMovie === movie.id) }"
              @click.stop="toggleActiveView(movie.id, 'synopsis')"
              title="Sinopse"
            >
              <i class="pi pi-file-edit"></i>
            </button>
            <button
              class="control-icon"
              :class="{ active: clickedView[movie.id] === 'cast' }"
              @click.stop="toggleActiveView(movie.id, 'cast')"
              title="Personagens"
            >
              <i class="pi pi-users"></i>
            </button>
            <button
              class="control-icon"
              :class="{ active: clickedView[movie.id] === 'info' }"
              @click.stop="toggleActiveView(movie.id, 'info')"
              title="Informações"
            >
              <i class="pi pi-info-circle"></i>
            </button>
          </div>

          <!-- Overlay com conteúdo (sinopse, cast ou info) -->
          <div
            class="card-overlay"
            :class="{
              'show-synopsis': activeView[movie.id] === 'synopsis',
              'show-cast': activeView[movie.id] === 'cast',
              'show-info': activeView[movie.id] === 'info',
              'visible': activeView[movie.id] !== null && activeView[movie.id] !== undefined
            }"
          >
            <!-- Sinopse (padrão ao passar mouse) -->
            <div v-if="activeView[movie.id] === 'synopsis'" class="overlay-content synopsis-content">
              <h4>{{ movie.title }}</h4>
              <p>{{ movie.overview || 'Sem descrição disponível' }}</p>
            </div>

            <!-- Elenco (6 principais atores) -->
            <div v-if="activeView[movie.id] === 'cast'" class="overlay-content cast-content">
              <h4>Elenco Principal</h4>
              <div v-if="loadingCast[movie.id]" class="loading-cast">
                <ProgressSpinner />
                <p>Carregando...</p>
              </div>
              <div v-else-if="movieCast[movie.id] && movieCast[movie.id].length > 0" class="cast-grid">
                <div
                  v-for="actor in movieCast[movie.id].slice(0, 9)"
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
            <div v-if="activeView[movie.id] === 'info'" class="overlay-content info-content">
              <h4>Informações</h4>
              <div v-if="loadingInfo[movie.id]" class="loading-info">
                <ProgressSpinner />
                <p>Carregando...</p>
              </div>
              <div v-else-if="movieInfo[movie.id]" class="info-list">
                <div class="info-item">
                  <strong>Ano de Lançamento:</strong>
                  <span>{{ getYear(movieInfo[movie.id].release_date) }}</span>
                </div>
                <div class="info-item">
                  <strong>Gêneros:</strong>
                  <span>{{ getGenres(movieInfo[movie.id].genres) }}</span>
                </div>
                <div v-if="getDirector(movieInfo[movie.id])" class="info-item">
                  <strong>Direção:</strong>
                  <span>{{ getDirector(movieInfo[movie.id]) }}</span>
                </div>
                <div v-if="movieInfo[movie.id].certification || movieInfo[movie.id].adult !== undefined" class="info-item">
                  <strong>Classificação:</strong>
                  <span>{{ movieInfo[movie.id].adult ? '18+' : 'Livre' }}</span>
                </div>
                <div v-if="movieInfo[movie.id].runtime" class="info-item">
                  <strong>Duração:</strong>
                  <span>{{ movieInfo[movie.id].runtime }} minutos</span>
                </div>
                <div v-if="movieInfo[movie.id].vote_average" class="info-item">
                  <strong>Avaliação:</strong>
                  <span>{{ movieInfo[movie.id].vote_average.toFixed(1) }}/10</span>
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
import { ref, onMounted, computed } from 'vue'
import { TransitionGroup } from 'vue'
import { useFilmStore } from '@/stores/film'
import { useFavoriteStore } from '@/stores/favorite'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import ProgressSpinner from 'primevue/progressspinner'
import Dialog from 'primevue/dialog'
import Select from 'primevue/select'
import { useToast } from 'primevue/usetoast'
import dayjs from 'dayjs'

const filmStore = useFilmStore()
const favoriteStore = useFavoriteStore()
const authStore = useAuthStore()
const router = useRouter()
const toast = useToast()

const searchInput = ref('')
const hoveredMovie = ref(null)
const activeView = ref({}) // 'synopsis', 'cast', 'info'
const clickedView = ref({}) // Para manter view ativa mesmo sem hover
const movieCast = ref({})
const movieInfo = ref({})
const loadingCast = ref({})
const loadingInfo = ref({})

// Filtros
const filtersModalVisible = ref(false)
const selectedGenre = ref(null)
const selectedYear = ref(null)
const currentYear = computed(() => new Date().getFullYear())

// Ordenação
const sortModalVisible = ref(false)
const selectedSort = ref('popularity.desc')

const sortOptions = [
  { label: 'Popularidade (Mais Popular)', value: 'popularity.desc', icon: '🔥' },
  { label: 'Popularidade (Menos Popular)', value: 'popularity.asc', icon: '📉' },
  { label: 'Avaliação (Melhor Avaliado)', value: 'vote_average.desc', icon: '⭐' },
  { label: 'Avaliação (Pior Avaliado)', value: 'vote_average.asc', icon: '📊' },
  { label: 'Data de Lançamento (Mais Recente)', value: 'release_date.desc', icon: '📅' },
  { label: 'Data de Lançamento (Mais Antigo)', value: 'release_date.asc', icon: '📆' },
  { label: 'Número de Votos (Mais Votados)', value: 'vote_count.desc', icon: '👍' },
  { label: 'Número de Votos (Menos Votados)', value: 'vote_count.asc', icon: '👎' }
]

// Opções de anos (de 1990 até o ano atual)
const yearOptions = computed(() => {
  const years = []
  for (let year = 1990; year <= currentYear.value; year++) {
    years.push(year)
  }
  return years.reverse() // Mais recentes primeiro
})

// Opções formatadas para os Selects
const genreOptions = computed(() => {
  return filmStore.genres.map(genre => ({
    label: genre.name,
    value: genre.id
  }))
})

const yearDropdownOptions = computed(() => {
  const options = yearOptions.value.map(year => ({
    label: year.toString(),
    value: year
  }))
  options.push({
    label: 'Anterior à 1990',
    value: 'before-1990'
  })
  return options
})

// Filmes exibidos (limitados a 15)
const displayedMovies = computed(() => {
  return filmStore.movies.slice(0, 15)
})

onMounted(async () => {
  // Só buscar favoritos se o usuário estiver autenticado
  if (authStore.isAuthenticated) {
    favoriteStore.fetchFavorites()
  }
  filmStore.fetchGenres()
  // Carregar todos os filmes ao iniciar
  await filmStore.discoverMovies({ genre: null, year: null }, 1, 'popularity.desc')
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

async function handleClear() {
  searchInput.value = ''
  filmStore.clearSearch()
  hoveredMovie.value = null
  activeView.value = {}
  clickedView.value = {}
  movieCast.value = {}
  movieInfo.value = {}
  // Recarregar filmes iniciais (mesmo comportamento do onMounted)
  await filmStore.discoverMovies({ genre: null, year: null }, 1, 'popularity.desc')
}

function handleFilterChange() {
  // Pode ser usado para busca em tempo real se necessário
}

async function handleApplyFilters() {
  let year = null
  
  // Processar o ano selecionado
  if (selectedYear.value) {
    if (selectedYear.value === 'before-1990') {
      year = 'before-1990'
    } else {
      year = parseInt(selectedYear.value)
    }
  }

  const filters = {
    genre: selectedGenre.value,
    year: year
  }

  // Verificar se pelo menos um filtro está preenchido
  const hasFilters = Object.values(filters).some(v => v !== null && v !== '')

  if (!hasFilters) {
    toast.add({
      severity: 'warn',
      summary: 'Atenção',
      detail: 'Selecione pelo menos um filtro',
      life: 3000
    })
    return
  }

  filmStore.setFilters(filters)
  await filmStore.discoverMovies(filters, 1, selectedSort.value)
  filtersModalVisible.value = false
}

function handleApplySort() {
  const filters = filmStore.filters
  filmStore.discoverMovies(filters, filmStore.currentPage, selectedSort.value)
  sortModalVisible.value = false
}

async function handleClearFilters() {
  selectedGenre.value = null
  selectedYear.value = null
  filmStore.clearFilters()
  filmStore.clearSearch()
  // Recarregar filmes iniciais (mesmo comportamento do onMounted)
  await filmStore.discoverMovies({ genre: null, year: null }, 1, 'popularity.desc')
  filtersModalVisible.value = false
}

function handleMouseEnter(movie) {
  hoveredMovie.value = movie.id
  // Se há uma view clicada, usar ela; senão, mostrar sinopse por padrão
  if (clickedView.value[movie.id]) {
    activeView.value[movie.id] = clickedView.value[movie.id]
  } else {
    // Mostrar sinopse por padrão ao passar o mouse
    activeView.value[movie.id] = 'synopsis'
  }
}

function handleMouseLeave(movieId) {
  hoveredMovie.value = null
  // Sempre limpar a view ativa quando o mouse sair, mesmo se houver view clicada
  // A view clicada será restaurada quando o mouse voltar
  activeView.value[movieId] = null
}

function setActiveView(movieId, view) {
  // Marcar como view clicada para manter ativa mesmo sem hover
  clickedView.value[movieId] = view
  activeView.value[movieId] = view
  
  if (view === 'cast' && !movieCast.value[movieId]) {
    loadCast(movieId)
  } else if (view === 'info' && !movieInfo.value[movieId]) {
    loadInfo(movieId)
  } else if (view === 'synopsis') {
    // Sinopse já está disponível
  }
}

// Função para fechar a view ativa (pode ser chamada ao clicar novamente no mesmo ícone)
function toggleActiveView(movieId, view) {
  if (clickedView.value[movieId] === view) {
    // Se já está ativa, desativar
    clickedView.value[movieId] = null
    activeView.value[movieId] = null
  } else {
    // Ativar a view
    setActiveView(movieId, view)
  }
}

async function loadCast(movieId) {
  if (loadingCast.value[movieId]) return
  
  loadingCast.value[movieId] = true
  try {
    const details = await filmStore.getMovieDetails(movieId)
    if (details && details.credits && details.credits.cast) {
      movieCast.value[movieId] = details.credits.cast
    } else {
      movieCast.value[movieId] = []
    }
  } catch (error) {
    console.error('Erro ao carregar elenco:', error)
    movieCast.value[movieId] = []
  } finally {
    loadingCast.value[movieId] = false
  }
}

async function loadInfo(movieId) {
  if (loadingInfo.value[movieId]) return
  
  loadingInfo.value[movieId] = true
  try {
    const details = await filmStore.getMovieDetails(movieId)
    if (details) {
      movieInfo.value[movieId] = details
    } else {
      movieInfo.value[movieId] = null
    }
  } catch (error) {
    console.error('Erro ao carregar informações:', error)
    movieInfo.value[movieId] = null
  } finally {
    loadingInfo.value[movieId] = false
  }
}

async function toggleFavorite(movie) {
  // Verificar se o usuário está autenticado
  if (!authStore.isAuthenticated || !authStore.user) {
    router.push('/login')
    return
  }

  if (favoriteStore.isFavorite(movie.id)) {
    // Remover dos favoritos
    const favoriteId = favoriteStore.getFavoriteId(movie.id)
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
        detail: result.error || 'Erro ao remover dos favoritos',
        life: 3000
      })
    }
  } else {
    // Adicionar aos favoritos
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
        detail: result.error || 'Erro ao adicionar aos favoritos',
        life: 3000
      })
    }
  }
}

async function goToPage(page) {
  if (page >= 1 && page <= filmStore.totalPages) {
    // Se há busca ativa, usar search; senão, usar discover
    const hasSearchQuery = filmStore.searchQuery && filmStore.searchQuery.trim() !== ''
    
    if (hasSearchQuery) {
      await filmStore.searchMovies(filmStore.searchQuery, page)
    } else {
      // Sem busca, usar discover (com ou sem filtros)
      await filmStore.discoverMovies(filmStore.filters, page, selectedSort.value)
    }
  }
}

function formatDate(dateString) {
  if (!dateString) return 'Data não disponível'
  return dayjs(dateString).format('DD/MM/YYYY')
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

.search-section {
  margin-bottom: 2rem;
}

.search-container {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  align-items: flex-end;
}

.search-box {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.filters-button-section {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
}

.search-input {
  width: 300px;
  max-width: 300px;
}

/* Estilos da Modal de Filtros */
:deep(.filters-modal) {
  border-radius: 12px;
  overflow: hidden;
}

:deep(.filters-modal .p-dialog-header) {
  background: #ffffff;
  border-bottom: 1px solid #e9ecef;
  padding: 1.5rem;
  border-radius: 12px 12px 0 0;
}

:deep(.filters-modal .p-dialog-title) {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1e293b;
}

:deep(.filters-modal .p-dialog-content) {
  padding: 1.5rem;
  background: #ffffff;
}

:deep(.filters-modal .p-dialog-footer) {
  background: #ffffff;
  border-top: 1px solid #e9ecef;
  padding: 1rem 1.5rem;
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  border-radius: 0 0 12px 12px;
}

.filters-section {
  padding: 0;
}

.filters-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
}

.filter-item {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.filter-item label {
  font-size: 0.9rem;
  font-weight: 500;
  color: #475569;
  margin-bottom: 0.25rem;
}

.filter-input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.95rem;
  background: #ffffff;
  color: #1e293b;
  transition: all 0.2s;
}

/* Estilos para Select do PrimeVue */
:deep(.filter-dropdown) {
  width: 100%;
}

:deep(.filter-dropdown .p-select) {
  width: 100%;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: #ffffff;
}

:deep(.filter-dropdown .p-select-label) {
  padding: 0.75rem;
  color: #1e293b;
  font-size: 0.95rem;
}

:deep(.filter-dropdown .p-select-overlay) {
  max-height: 200px !important;
  overflow-y: auto;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

:deep(.filter-dropdown .p-select-list) {
  max-height: 200px !important;
  overflow-y: auto;
}

:deep(.filter-dropdown .p-select-item) {
  padding: 0.75rem;
  color: #1e293b;
  font-size: 0.95rem;
}

:deep(.filter-dropdown .p-select-item:hover) {
  background: #f1f5f9;
}

:deep(.filter-dropdown .p-select-item.p-highlight) {
  background: #eef2ff;
  color: #6366f1;
}

.filter-input:focus {
  outline: none;
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

:deep(.filter-dropdown .p-select:focus-within) {
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

/* Botões da modal */
:deep(.clear-button) {
  background: #ffffff;
  color: #6366f1;
  border: 1px solid #6366f1;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 500;
  transition: all 0.2s;
}

:deep(.clear-button:hover) {
  background: #eef2ff;
  border-color: #6366f1;
}

:deep(.filter-button) {
  background: #6366f1;
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 500;
  transition: all 0.2s;
}

:deep(.filter-button:hover) {
  background: #4f46e5;
}

/* Estilos da Modal de Ordenação */
:deep(.sort-modal) {
  border-radius: 12px;
  overflow: hidden;
}

:deep(.sort-modal .p-dialog-header) {
  background: #ffffff;
  border-bottom: 1px solid #e9ecef;
  padding: 1.5rem;
  border-radius: 12px 12px 0 0;
}

:deep(.sort-modal .p-dialog-title) {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1e293b;
}

:deep(.sort-modal .p-dialog-content) {
  padding: 1.5rem;
  background: #ffffff;
}

:deep(.sort-modal .p-dialog-footer) {
  background: #ffffff;
  border-top: 1px solid #e9ecef;
  padding: 1rem 1.5rem;
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  border-radius: 0 0 12px 12px;
}

.sort-section {
  padding: 0;
}

.sort-options {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.sort-option {
  display: flex;
  align-items: center;
  padding: 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  background: #ffffff;
}

.sort-option:hover {
  border-color: #6366f1;
  background: #f8f9ff;
}

.sort-option:has(.sort-radio:checked) {
  border-color: #6366f1;
  background: #eef2ff;
}

.sort-radio {
  margin: 0;
  margin-right: 0.75rem;
  width: 18px;
  height: 18px;
  cursor: pointer;
  accent-color: #6366f1;
}

.sort-label {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
  flex: 1;
  font-size: 1rem;
  color: #1e293b;
}

.sort-icon {
  font-size: 1.25rem;
}

.sort-text {
  font-weight: 500;
}

:deep(.cancel-button) {
  background: #ffffff;
  color: #6366f1;
  border: 1px solid #6366f1;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 500;
  transition: all 0.2s;
}

:deep(.cancel-button:hover) {
  background: #eef2ff;
  border-color: #6366f1;
}

:deep(.apply-button) {
  background: #6366f1;
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 500;
  transition: all 0.2s;
}

:deep(.apply-button:hover) {
  background: #4f46e5;
}

.filters-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
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
  color: rgba(255, 255, 255, 0.9);
  box-shadow: none;
  backdrop-filter: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  animation: starFloat 3s ease-in-out infinite;
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

@keyframes starFloat {
  0%, 100% {
    transform: translate(0, 0) rotate(0deg);
  }
  25% {
    transform: translate(2px, -3px) rotate(2deg);
  }
  50% {
    transform: translate(-2px, -2px) rotate(-2deg);
  }
  75% {
    transform: translate(1px, -4px) rotate(1deg);
  }
}

.favorite-star i {
  font-size: 32px;
  filter: drop-shadow(0 2px 6px rgba(0, 0, 0, 0.6));
}

.favorite-star:hover {
  background: none;
  color: rgba(255, 255, 255, 1);
  transform: scale(1.2);
  animation: none;
}

.favorite-star.is-favorite {
  background: none;
  color: #ffc107;
  border: none;
  animation: starPulse 2s ease-in-out infinite;
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

.favorite-star.is-favorite i {
  filter: drop-shadow(0 2px 8px rgba(255, 193, 7, 0.9));
}

.favorite-star.is-favorite:hover {
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
  padding-top: 80px; /* Margem para iniciar abaixo dos botões */
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
