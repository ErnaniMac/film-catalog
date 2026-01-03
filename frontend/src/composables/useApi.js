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
      error._isValidationError = true
      // Silenciar o log do axios para 422, mas ainda rejeitar a promise
      // para que o componente possa tratar a validação
      return Promise.reject(error)
    }
    
    return Promise.reject(error)
  }
)

// Interceptar console.error apenas para erros relacionados ao axios com status 422
// Isso evita que erros de validação apareçam no console como erros
if (typeof window !== 'undefined') {
  const originalConsoleError = console.error
  console.error = function(...args) {
    // Verificar se é um erro relacionado ao axios com status 422
    if (args.length > 0) {
      const firstArg = args[0]
      
      // Verificar se é um erro do axios com status 422
      if (firstArg?.response?.status === 422 || firstArg?._isValidationError === true) {
        // Silenciar erros 422 - não logar no console
        return
      }
      
      // Verificar se é uma mensagem de erro do axios contendo 422
      if (typeof firstArg === 'string') {
        // Verificar se é uma mensagem de erro HTTP do navegador com 422
        const is422Error = /422|Unprocessable|validation/i.test(firstArg)
        // Verificar se é uma requisição HTTP com 422
        const isHttp422 = /POST|GET|PUT|DELETE|PATCH.*422/i.test(firstArg)
        
        if (is422Error || isHttp422) {
          // Silenciar mensagens de erro 422
          return
        }
      }
    }
    
    // Para outros erros, usar o console.error normal
    originalConsoleError.apply(console, args)
  }
}

export default api

