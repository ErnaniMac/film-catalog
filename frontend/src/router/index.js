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
  
  // Se tem token mas não tem usuário, buscar usuário
  if (authStore.token && !authStore.user) {
    try {
      await authStore.fetchUser()
    } catch (error) {
      // Se falhar, limpar token
      authStore.logout()
    }
  }
  
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next({ name: 'login' })
  } else if (to.meta.requiresGuest && authStore.isAuthenticated) {
    next({ name: 'home' })
  } else if (to.meta.requiresAdmin && !authStore.isAdmin) {
    next({ name: 'home' })
  } else {
    next()
  }
})

export default router

