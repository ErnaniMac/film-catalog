import { createApp } from 'vue'
import { createPinia } from 'pinia'
import PrimeVue from 'primevue/config'
import Aura from '@primevue/themes/aura'
import ToastService from 'primevue/toastservice'
import 'primeicons/primeicons.css'
import App from './App.vue'
import router from './router'
import { useAuthStore } from './stores/auth'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)
app.use(PrimeVue, {
  theme: {
    preset: Aura
  }
})
app.use(ToastService)

// Validar token ao iniciar a aplicação
const authStore = useAuthStore()
if (authStore.token) {
  authStore.fetchUser().catch(() => {
    // Se falhar, token é inválido - será limpo pelo fetchUser
  })
}

app.mount('#app')

