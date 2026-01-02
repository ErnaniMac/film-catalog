import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/composables/useApi'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('token') || null)

  const isAuthenticated = computed(() => !!token.value)
  const isAdmin = computed(() => user.value?.roles?.some(role => role.name === 'admin') || false)

  async function login(email, password) {
    try {
      const response = await api.post('/login', { email, password })
      
      // Verificar se o e-mail não foi verificado
      if (response.status === 403 && response.data?.email_verified === false) {
        return { 
          success: false, 
          error: response.data.message || 'E-mail não verificado',
          emailNotVerified: true,
          userId: response.data.user_id
        }
      }
      
      token.value = response.data.token
      user.value = response.data.user
      localStorage.setItem('token', token.value)
      return { success: true }
    } catch (error) {
      return { success: false, error: error.response?.data?.message || 'Erro ao fazer login' }
    }
  }

  async function logout() {
    try {
      await api.post('/logout')
    } catch (error) {
      console.error('Erro ao fazer logout:', error)
    } finally {
      token.value = null
      user.value = null
      localStorage.removeItem('token')
    }
  }

  async function fetchUser() {
    try {
      const response = await api.get('/user')
      user.value = response.data
      return { success: true }
    } catch (error) {
      console.error('Erro ao buscar usuário:', error)
      logout()
      return { success: false }
    }
  }

  return {
    user,
    token,
    isAuthenticated,
    isAdmin,
    login,
    logout,
    fetchUser
  }
})

