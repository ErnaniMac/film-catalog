<template>
  <div id="app">
    <Toast />
    <Navbar v-if="showNavbar" />
    <RouterView />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { RouterView } from 'vue-router'
import Toast from 'primevue/toast'
import Navbar from '@/components/common/Navbar.vue'

const route = useRoute()
const authStore = useAuthStore()

const showNavbar = computed(() => {
  // Não mostrar navbar em rotas de guest (login, register, etc)
  const guestRoutes = ['login', 'register', 'forgot-password', 'reset-password', 'verify-email']
  if (guestRoutes.includes(route.name)) {
    return false
  }
  // Mostrar navbar apenas se usuário estiver autenticado
  return authStore.isAuthenticated && !!authStore.user
})
</script>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
  background: #f8f9fa;
  color: #333;
}

#app {
  min-height: 100vh;
  background: #f8f9fa;
}
</style>

