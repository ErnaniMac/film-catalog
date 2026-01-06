import axios from 'axios'
import { useAuthStore } from '@/stores/auth'

// Interceptar XMLHttpRequest e fetch antes do axios para suprimir logs 422
if (typeof window !== 'undefined') {
  // Interceptar XMLHttpRequest
  if (window.XMLHttpRequest) {
    const OriginalXHR = window.XMLHttpRequest
    window.XMLHttpRequest = function() {
      const xhr = new OriginalXHR()
      const originalOpen = xhr.open
      const originalSend = xhr.send
      
      xhr.open = function(method, url, ...args) {
        return originalOpen.apply(this, [method, url, ...args])
      }
      
      xhr.send = function(...args) {
        const originalOnReadyStateChange = xhr.onreadystatechange
        
        xhr.onreadystatechange = function() {
          // Suprimir logs para status 422
          if (xhr.readyState === 4 && xhr.status === 422) {
            // Silenciar completamente mas permitir que o axios receba a resposta
            if (originalOnReadyStateChange) {
              originalOnReadyStateChange.apply(this, arguments)
            }
            return
          }
          
          if (originalOnReadyStateChange) {
            originalOnReadyStateChange.apply(this, arguments)
          }
        }
        
        return originalSend.apply(this, args)
      }
      
      return xhr
    }
  }
  
  // Interceptar fetch também (axios pode usar fetch em alguns casos)
  if (window.fetch) {
    const originalFetch = window.fetch
    window.fetch = function(...args) {
      return originalFetch.apply(this, args).then(response => {
        // Se for 422, não logar mas retornar a resposta normalmente
        if (response.status === 422) {
          // Marcar para não ser logado
          response._isValidationError = true
        }
        return response
      }).catch(error => {
        // Suprimir logs de erro 422
        if (error?.response?.status === 422 || error?.status === 422) {
          error._isValidationError = true
        }
        throw error
      })
    }
  }
  
  // Interceptar console.info também (alguns navegadores usam isso)
  const originalConsoleInfo = console.info
  console.info = function(...args) {
    const firstArg = args[0]
    if (typeof firstArg === 'string' && /422|Unprocessable/i.test(firstArg)) {
      return // Silenciar
    }
    originalConsoleInfo.apply(console, args)
  }
}

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  // Suprimir logs automáticos do axios para erros 422
  validateStatus: function (status) {
    // Retornar true para 422 para que não seja tratado como erro pelo axios
    // Mas ainda vamos rejeitar manualmente no interceptor para manter o comportamento
    return status >= 200 && status < 500
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
  (response) => {
    // Se for 422, tratar como erro mas sem logar
    if (response.status === 422) {
      const error = new Error('Validation Error')
      error.response = response
      error._isValidationError = true
      return Promise.reject(error)
    }
    return response
  },
  (error) => {
    if (error.response?.status === 401) {
      const authStore = useAuthStore()
      // Só fazer logout e redirecionar se ainda houver token (evita loop)
      if (authStore.token) {
        authStore.logout()
        // Não redirecionar se já estiver na página de login ou se o logout foi intencional
        if (!window.location.pathname.includes('/login')) {
          window.location.href = '/login'
        }
      }
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

// Interceptar console.error, console.log e console.warn para silenciar erros 422
// Isso evita que erros de validação apareçam no console
if (typeof window !== 'undefined') {
  // Função auxiliar para verificar se é um erro 422
  const is422Error = (...args) => {
    if (args.length === 0) return false
    
    // Verificar todos os argumentos
    for (const arg of args) {
      // Verificar se é um erro do axios com status 422
      if (arg?.response?.status === 422 || arg?._isValidationError === true) {
        return true
      }
      
      // Verificar se é uma string contendo 422 ou Unprocessable
      if (typeof arg === 'string') {
        // Padrões mais abrangentes para capturar qualquer mensagem relacionada a 422
        const is422Pattern = /422|Unprocessable\s+Content|validation/i.test(arg)
        // Capturar requisições HTTP com 422 (POST, GET, PUT, DELETE, PATCH)
        const isHttp422 = /(POST|GET|PUT|DELETE|PATCH|OPTIONS)\s+.*422/i.test(arg)
        // Capturar URLs com 422 no status
        const isUrl422 = /http.*422/i.test(arg)
        // Capturar qualquer mensagem que contenha "422" seguido de parênteses ou espaço
        const isStatus422 = /\b422\b/i.test(arg)
        
        if (is422Pattern || isHttp422 || isUrl422 || isStatus422) {
          return true
        }
      }
      
      // Verificar se é um objeto com mensagem contendo 422
      if (arg && typeof arg === 'object') {
        const message = arg.message || arg.msg || arg.error || arg.toString?.() || ''
        if (typeof message === 'string' && /422|Unprocessable/i.test(message)) {
          return true
        }
        // Verificar propriedades do objeto
        if (arg.status === 422 || arg.statusCode === 422) {
          return true
        }
      }
    }
    
    return false
  }
  
  // Interceptar console.error
  const originalConsoleError = console.error
  console.error = function(...args) {
    if (is422Error(...args)) {
      return // Silenciar erros 422
    }
    originalConsoleError.apply(console, args)
  }
  
  // Interceptar console.log
  const originalConsoleLog = console.log
  console.log = function(...args) {
    if (is422Error(...args)) {
      return // Silenciar logs 422
    }
    originalConsoleLog.apply(console, args)
  }
  
  // Interceptar console.warn
  const originalConsoleWarn = console.warn
  console.warn = function(...args) {
    if (is422Error(...args)) {
      return // Silenciar warnings 422
    }
    originalConsoleWarn.apply(console, args)
  }
}

export default api


