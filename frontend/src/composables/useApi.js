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
      // Marcar o erro como "silenciado" para evitar logs no console
      // O navegador ainda mostrará na aba Network, mas não como erro no console
      error._isValidationError = true
      // Silenciar o log do axios para 422, mas ainda rejeitar a promise
      // para que o componente possa tratar a validação
      return Promise.reject(error)
    }
    
    return Promise.reject(error)
  }
)

// Interceptar console.error para filtrar erros 422 do axios
const originalConsoleError = console.error
console.error = function(...args) {
  // Verificar se é um erro do axios com status 422
  if (args.length > 0) {
    const firstArg = args[0]
    // Verificar se é uma string contendo "422" ou um objeto de erro do axios
    if (
      (typeof firstArg === 'string' && firstArg.includes('422')) ||
      (firstArg?.response?.status === 422) ||
      (firstArg?._isValidationError === true)
    ) {
      // Silenciar erros 422 - não logar no console
      return
    }
  }
  // Para outros erros, usar o console.error normal
  originalConsoleError.apply(console, args)
}

export default api

