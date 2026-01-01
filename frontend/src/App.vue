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
  return route.name !== 'login'
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
}
</style>

