import axios from 'axios'
import { useAuthStore } from '@/stores/auth'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Request interceptor
api.interceptors.request.use(
  (config) => {
    const authStore = useAuthStore()
    if (authStore.token) {
      config.headers.Authorization = `Bearer ${authStore.token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      const authStore = useAuthStore()
      authStore.logout()
      window.location.href = '/login'
    }
    
    // Não logar erros de validação (422) como erros no console
    // Eles são respostas válidas do servidor indicando problemas de validação
    if (error.response?.status === 422) {
      // Silenciar o log do axios para 422, mas ainda rejeitar a promise
      // para que o componente possa tratar a validação
      return Promise.reject(error)
    }
    
    return Promise.reject(error)
  }
)

export default api

