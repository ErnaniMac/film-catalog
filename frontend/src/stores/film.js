import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/composables/useApi'

export const useFilmStore = defineStore('film', () => {
  const movies = ref([])
  const loading = ref(false)
  const searchQuery = ref('')
  const currentPage = ref(1)
  const totalPages = ref(1)
  const genres = ref([])
  const filters = ref({
    genre: null,
    year: null
  })

  async function searchMovies(query, page = 1) {
    if (!query.trim()) {
      movies.value = []
      return
    }

    loading.value = true
    searchQuery.value = query

    try {
      const response = await api.get('/tmdb/search', {
        params: { query, page }
      })

      movies.value = response.data.results || []
      currentPage.value = response.data.page || 1
      totalPages.value = response.data.total_pages || 1
    } catch (error) {
      console.error('Erro ao buscar filmes:', error)
      movies.value = []
    } finally {
      loading.value = false
    }
  }

  function clearSearch() {
    movies.value = []
    searchQuery.value = ''
    currentPage.value = 1
    totalPages.value = 1
  }

  async function getMovieDetails(movieId) {
    try {
      const response = await api.get('/tmdb/details', {
        params: { id: movieId }
      })
      return response.data
    } catch (error) {
      console.error('Erro ao buscar detalhes do filme:', error)
      return null
    }
  }

  async function discoverMovies(filters, page = 1, sortBy = 'popularity.desc') {
    loading.value = true

    try {
      const params = {
        page,
        language: 'pt-BR',
        sort_by: sortBy
      }

      if (filters.genre) {
        params.with_genres = filters.genre
      }

      if (filters.year) {
        if (filters.year === 'before-1990') {
          params['primary_release_year'] = 'before-1990'
        } else {
          params.primary_release_year = filters.year
        }
      }

      console.log('📤 Parâmetros enviados para API:', params)

      // Construir query string manualmente para garantir que parâmetros com ponto sejam enviados corretamente
      // O Axios pode converter vote_average.gte em vote_average[gte] se não configurado corretamente
      const queryString = new URLSearchParams()
      Object.keys(params).forEach(key => {
        if (params[key] !== null && params[key] !== undefined && params[key] !== '') {
          queryString.append(key, params[key])
        }
      })
      
      console.log('🔗 Query string construída:', queryString.toString())

      // Usar a query string construída manualmente
      const response = await api.get(`/tmdb/discover?${queryString.toString()}`)
      
      console.log('📥 Resposta da API:', {
        totalResults: response.data.total_results,
        resultsCount: response.data.results?.length,
        page: response.data.page
      })

      movies.value = response.data.results || []
      currentPage.value = response.data.page || 1
      totalPages.value = response.data.total_pages || 1
    } catch (error) {
      console.error('Erro ao buscar filmes:', error)
      movies.value = []
    } finally {
      loading.value = false
    }
  }

  async function fetchGenres() {
    try {
      const response = await api.get('/tmdb/genres')
      genres.value = response.data.genres || []
    } catch (error) {
      console.error('Erro ao buscar gêneros:', error)
      genres.value = []
    }
  }

  function setFilters(newFilters) {
    filters.value = { ...filters.value, ...newFilters }
  }

  function clearFilters() {
    filters.value = {
      genre: null,
      year: null,
      rating: null,
      certification: null
    }
  }

  return {
    movies,
    loading,
    searchQuery,
    currentPage,
    totalPages,
    genres,
    filters,
    searchMovies,
    clearSearch,
    getMovieDetails,
    discoverMovies,
    fetchGenres,
    setFilters,
    clearFilters
  }
})

