import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/composables/useApi'

export const useFilmStore = defineStore('film', () => {
  const movies = ref([])
  const loading = ref(false)
  const searchQuery = ref('')
  const currentPage = ref(1)
  const totalPages = ref(1)

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

  return {
    movies,
    loading,
    searchQuery,
    currentPage,
    totalPages,
    searchMovies,
    clearSearch
  }
})

