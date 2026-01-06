import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'home',
      component: () => import('@/views/Home.vue')
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('@/views/Login.vue'),
      meta: { requiresGuest: true }
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('@/views/Register.vue'),
      meta: { requiresGuest: true }
    },
    {
      path: '/forgot-password',
      name: 'forgot-password',
      component: () => import('@/views/ForgotPassword.vue'),
      meta: { requiresGuest: true }
    },
    {
      path: '/reset-password',
      name: 'reset-password',
      component: () => import('@/views/ResetPassword.vue'),
      meta: { requiresGuest: true }
    },
    {
      path: '/verify-email',
      name: 'verify-email',
      component: () => import('@/views/VerifyEmail.vue'),
      meta: { requiresGuest: true }
    },
    {
      path: '/auth/google/callback',
      name: 'google-callback',
      component: () => import('@/views/GoogleCallback.vue')
    },
    {
      path: '/films',
      name: 'films',
      component: () => import('@/views/Films.vue')
    },
    {
      path: '/favorites',
      name: 'favorites',
      component: () => import('@/views/Favorites.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/settings',
      name: 'settings',
      component: () => import('@/views/Settings.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/admin',
      name: 'admin',
      component: () => import('@/views/Admin.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    }
  ]
})

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  
  // Se está tentando acessar login ou register, sempre permitir (mesmo com token inválido)
  if (to.name === 'login' || to.name === 'register') {
    // Se tem token mas não tem usuário, limpar antes de permitir acesso
    if (authStore.token && !authStore.user) {
      authStore.clearAuth()
    }
    next()
    return
  }
  
  // Se tem token mas não tem usuário, validar token buscando usuário
  if (authStore.token && !authStore.user && !authStore.isLoading) {
    try {
      const result = await authStore.fetchUser()
      // Se falhar ao buscar usuário, token é inválido - limpar tudo
      if (!result.success) {
        authStore.clearAuth()
      }
    } catch (error) {
      // Se falhar, limpar token
      authStore.clearAuth()
    }
  }
  
  // Rota home: sempre redireciona para /films
  if (to.name === 'home') {
    next({ name: 'films' })
    return
  }
  
  // Rotas que requerem autenticação
  if (to.meta.requiresAuth) {
    if (!authStore.isAuthenticated || !authStore.user || !authStore.user.name) {
      next({ name: 'login' })
      return
    }
  }
  
  // Rotas que requerem ser guest (não autenticado)
  if (to.meta.requiresGuest) {
    // Permitir acesso se não estiver autenticado OU se o token for inválido
    if (authStore.isAuthenticated && authStore.user && authStore.user.name) {
      next({ name: 'films' })
      return
    }
    // Se tem token mas não tem usuário válido, limpar e permitir acesso
    if (authStore.token && !authStore.user) {
      authStore.clearAuth()
    }
  }
  
  // Rotas que requerem admin
  if (to.meta.requiresAdmin) {
    if (!authStore.isAuthenticated || !authStore.user || !authStore.isAdmin) {
      next({ name: 'films' })
      return
    }
  }
  
  next()
})

export default router

