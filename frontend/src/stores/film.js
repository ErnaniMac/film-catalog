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
    year: null,
    rating: null,
    certification: null
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

  async function discoverMovies(filters, page = 1) {
    loading.value = true

    try {
      const params = {
        page,
        language: 'pt-BR'
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

      if (filters.rating !== null && filters.rating !== undefined && filters.rating !== '') {
        params['vote_average.gte'] = parseFloat(filters.rating)
      }

      if (filters.certification) {
        params.certification_country = 'BR'
        params.certification = filters.certification
      }

      const response = await api.get('/tmdb/discover', { params })

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

