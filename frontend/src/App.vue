<template>
  <div id="app">
    <Toast :breakpoints="{ '960px': { width: '100%', right: '0', left: '0' } }" />
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

/* Estilos responsivos para Toast (notificações) - Global */
.p-toast {
  max-width: calc(100vw - 2rem) !important;
  box-sizing: border-box !important;
}

.p-toast .p-toast-message {
  max-width: 100% !important;
  width: 100% !important;
  word-wrap: break-word !important;
  overflow-wrap: break-word !important;
  box-sizing: border-box !important;
}

.p-toast .p-toast-message-content {
  max-width: 100% !important;
  width: 100% !important;
  padding: 0.75rem 1rem !important;
  box-sizing: border-box !important;
  display: flex !important;
  align-items: flex-start !important;
}

.p-toast .p-toast-message-text {
  max-width: 100% !important;
  width: 100% !important;
  word-wrap: break-word !important;
  overflow-wrap: break-word !important;
  box-sizing: border-box !important;
  flex: 1 !important;
  min-width: 0 !important;
}

.p-toast .p-toast-summary {
  font-size: 1rem !important;
  font-weight: 600 !important;
  word-wrap: break-word !important;
  overflow-wrap: break-word !important;
  display: block !important;
  width: 100% !important;
}

.p-toast .p-toast-detail {
  font-size: 0.9rem !important;
  word-wrap: break-word !important;
  overflow-wrap: break-word !important;
  margin-top: 0.25rem !important;
  display: block !important;
  width: 100% !important;
}

/* Mobile */
@media (max-width: 640px) {
  .p-toast {
    width: calc(100vw - 1rem) !important;
    max-width: calc(100vw - 1rem) !important;
    left: 0.5rem !important;
    right: 0.5rem !important;
    margin: 0 !important;
  }

  .p-toast.p-toast-top-right,
  .p-toast.p-toast-top-left,
  .p-toast.p-toast-top-center {
    top: 0.5rem !important;
    left: 0.5rem !important;
    right: 0.5rem !important;
    width: calc(100vw - 1rem) !important;
    max-width: calc(100vw - 1rem) !important;
  }

  .p-toast.p-toast-bottom-right,
  .p-toast.p-toast-bottom-left,
  .p-toast.p-toast-bottom-center {
    bottom: 0.5rem !important;
    left: 0.5rem !important;
    right: 0.5rem !important;
    width: calc(100vw - 1rem) !important;
    max-width: calc(100vw - 1rem) !important;
  }

  .p-toast .p-toast-message {
    width: 100% !important;
    max-width: 100% !important;
    margin: 0.5rem 0 !important;
    box-sizing: border-box !important;
  }

  .p-toast .p-toast-message-content {
    padding: 0.75rem !important;
    flex-wrap: wrap !important;
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
  }

  .p-toast .p-toast-message-icon {
    font-size: 1.25rem !important;
    margin-right: 0.75rem !important;
    flex-shrink: 0 !important;
  }

  .p-toast .p-toast-message-text {
    flex: 1 !important;
    min-width: 0 !important;
    max-width: calc(100% - 2rem) !important;
    width: auto !important;
  }

  .p-toast .p-toast-summary {
    font-size: 0.9rem !important;
    line-height: 1.4 !important;
    word-break: break-word !important;
  }

  .p-toast .p-toast-detail {
    font-size: 0.85rem !important;
    line-height: 1.4 !important;
    margin-top: 0.25rem !important;
    word-break: break-word !important;
  }

  .p-toast .p-toast-icon-close {
    font-size: 1rem !important;
    margin-left: 0.5rem !important;
    flex-shrink: 0 !important;
  }
}

/* Tablet */
@media (max-width: 1024px) and (min-width: 641px) {
  .p-toast {
    max-width: calc(100vw - 2rem) !important;
    width: auto !important;
  }

  .p-toast .p-toast-message {
    max-width: 100% !important;
  }
}
</style>

