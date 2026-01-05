<template>
  <div id="app">
    <Toast />
    <Navbar v-if="showNavbar" />
    <RouterView />
    <Footer />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { RouterView } from 'vue-router'
import Toast from 'primevue/toast'
import Navbar from '@/components/common/Navbar.vue'
import Footer from '@/components/common/Footer.vue'

const route = useRoute()
const authStore = useAuthStore()

const showNavbar = computed(() => {
  // Mostrar navbar em todas as rotas
  return true
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
  display: flex;
  flex-direction: column;
}
</style>

