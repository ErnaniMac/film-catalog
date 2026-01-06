<template>
  <div class="callback-container">
    <div class="callback-card">
      <ProgressSpinner v-if="loading" />
      <div v-else-if="error" class="error-message">
        <h2>Erro ao autenticar</h2>
        <p>{{ error }}</p>
        <Button
          label="Voltar para Login"
          icon="pi pi-arrow-left"
          @click="$router.push('/login')"
        />
      </div>
      <div v-else class="success-message">
        <h2>Autenticado com sucesso!</h2>
        <p>Redirecionando...</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import Button from 'primevue/button'
import ProgressSpinner from 'primevue/progressspinner'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const loading = ref(true)
const error = ref('')

onMounted(async () => {
  try {
    // Verificar se há erro na URL
    if (route.query.error) {
      error.value = decodeURIComponent(route.query.error)
      loading.value = false
      return
    }

    const token = route.query.token
    const userParam = route.query.user

    if (!token || !userParam) {
      error.value = 'Parâmetros de autenticação não encontrados'
      loading.value = false
      return
    }

    // Decodificar dados do usuário
    let userData
    try {
      userData = typeof userParam === 'string' ? JSON.parse(decodeURIComponent(userParam)) : userParam
    } catch (e) {
      // Se já for objeto, usar diretamente
      userData = userParam
    }

    // Armazenar token e usuário
    authStore.token = token
    authStore.user = userData
    localStorage.setItem('token', token)

    // Buscar dados completos do usuário
    try {
      await authStore.fetchUser()
    } catch (e) {
      console.error('Erro ao buscar dados do usuário:', e)
    }

    // Redirecionar para /films após um breve delay
    setTimeout(() => {
      router.push('/films')
    }, 1500)
  } catch (err) {
    console.error('Erro ao processar callback do Google:', err)
    error.value = 'Erro ao processar autenticação. Tente novamente.'
    loading.value = false
  }
})
</script>

<style scoped>
.callback-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: calc(100vh - 200px);
  padding: 2rem 1rem;
  background: linear-gradient(135deg, #e0e7ff 0%, #f3e8ff 100%);
  flex: 1;
}

.callback-card {
  background: white;
  padding: 3rem;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  width: 100%;
  max-width: 400px;
  text-align: center;
}

.error-message h2 {
  color: #c33;
  margin-bottom: 1rem;
}

.error-message p {
  color: #666;
  margin-bottom: 1.5rem;
}

.success-message h2 {
  color: #3c3;
  margin-bottom: 1rem;
}

.success-message p {
  color: #666;
}
</style>

