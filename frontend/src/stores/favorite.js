import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/composables/useApi'

export const useFavoriteStore = defineStore('favorite', () => {
  const favorites = ref([])
  const loading = ref(false)
  const selectedGenre = ref(null)

  async function fetchFavorites(genreId = null) {
    loading.value = true
    selectedGenre.value = genreId

    try {
      const params = genreId ? { genre_id: genreId } : {}
      const response = await api.get('/favorites', { params })
      favorites.value = response.data.data || []
    } catch (error) {
      console.error('Erro ao buscar favoritos:', error)
      favorites.value = []
    } finally {
      loading.value = false
    }
  }

  async function addFavorite(movie) {
    try {
      const favoriteData = {
        tmdb_id: movie.id,
        title: movie.title,
        overview: movie.overview || '',
        poster: movie.poster_path ? `https://image.tmdb.org/t/p/w500${movie.poster_path}` : null,
        genre_ids: movie.genre_ids || []
      }

      await api.post('/favorites', favoriteData)
      await fetchFavorites(selectedGenre.value)
      return { success: true }
    } catch (error) {
      return {
        success: false,
        error: error.response?.data?.message || 'Erro ao adicionar favorito'
      }
    }
  }

  async function removeFavorite(favoriteId) {
    try {
      await api.delete(`/favorites/${favoriteId}`)
      await fetchFavorites(selectedGenre.value)
      return { success: true }
    } catch (error) {
      return {
        success: false,
        error: error.response?.data?.message || 'Erro ao remover favorito'
      }
    }
  }

  function isFavorite(tmdbId) {
    return favorites.value.some(fav => fav.tmdb_id === tmdbId)
  }

  function getFavoriteId(tmdbId) {
    const favorite = favorites.value.find(fav => fav.tmdb_id === tmdbId)
    return favorite?.id
  }

  function clearFavorites() {
    favorites.value = []
    selectedGenre.value = null
  }

  return {
    favorites,
    loading,
    selectedGenre,
    fetchFavorites,
    addFavorite,
    removeFavorite,
    isFavorite,
    getFavoriteId,
    clearFavorites
  }
})

