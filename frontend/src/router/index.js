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
      path: '/films',
      name: 'films',
      component: () => import('@/views/Films.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/favorites',
      name: 'favorites',
      component: () => import('@/views/Favorites.vue'),
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
  
  // Se tem token mas não tem usuário, validar token buscando usuário
  if (authStore.token && !authStore.user) {
    try {
      const result = await authStore.fetchUser()
      // Se falhar ao buscar usuário, token é inválido
      if (!result.success) {
        authStore.logout()
      }
    } catch (error) {
      // Se falhar, limpar token
      authStore.logout()
    }
  }
  
  // Rota home: redireciona baseado no estado de autenticação
  if (to.name === 'home') {
    if (authStore.isAuthenticated && authStore.user) {
      next({ name: 'films' })
    } else {
      next({ name: 'login' })
    }
    return
  }
  
  // Rotas que requerem autenticação
  if (to.meta.requiresAuth) {
    if (!authStore.isAuthenticated || !authStore.user) {
      next({ name: 'login' })
      return
    }
  }
  
  // Rotas que requerem ser guest (não autenticado)
  if (to.meta.requiresGuest) {
    if (authStore.isAuthenticated && authStore.user) {
      next({ name: 'films' })
      return
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

