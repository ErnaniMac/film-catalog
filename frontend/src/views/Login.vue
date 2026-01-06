<template>
  <div class="login-container">
    <div class="login-card">
      <h1>Login</h1>
      <p>Entre com suas credenciais</p>
      
      <form @submit.prevent="handleLogin" class="login-form">
        <div class="form-field">
          <label>Email</label>
          <InputText
            v-model="email"
            type="email"
            placeholder="seu@email.com"
            required
            class="full-width"
          />
        </div>
        
        <div class="form-field">
          <label>Senha</label>
          <InputText
            v-model="password"
            type="password"
            placeholder="Sua senha"
            required
            class="full-width"
          />
        </div>
        
        <Button
          type="submit"
          label="Entrar"
          icon="pi pi-sign-in"
          :loading="loading"
          class="full-width"
        />
      </form>

      <div class="divider">
        <span>ou</span>
      </div>

      <Button
        label="Continuar com Google"
        severity="secondary"
        outlined
        :loading="googleLoading"
        @click="handleGoogleAuth"
        class="full-width google-button"
      >
        <template #icon>
          <svg width="18" height="18" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg" style="margin-right: 0.5rem;">
            <path fill="#4285F4" d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.874 2.684-6.615z"/>
            <path fill="#34A853" d="M9 18c2.43 0 4.467-.806 5.956-2.184l-2.908-2.258c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332C2.438 15.983 5.482 18 9 18z"/>
            <path fill="#FBBC05" d="M3.964 10.707c-.18-.54-.282-1.117-.282-1.707s.102-1.167.282-1.707V4.961H.957C.347 6.174 0 7.55 0 9s.348 2.826.957 4.039l3.007-2.332z"/>
            <path fill="#EA4335" d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0 5.482 0 2.438 2.017.957 4.961L3.964 7.293C4.672 5.163 6.656 3.58 9 3.58z"/>
          </svg>
        </template>
      </Button>

      <div v-if="error" class="error-message">
        {{ error }}
      </div>

      <div class="links">
        <p>
          <router-link to="/register">Criar conta</router-link> |
          <router-link to="/forgot-password">Esqueci minha senha</router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import api from '@/composables/useApi'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const email = ref('')
const password = ref('')
const loading = ref(false)
const googleLoading = ref(false)
const error = ref('')

// Verificar se há erro na query string (vindo do callback do Google)
onMounted(() => {
  if (route.query.error) {
    error.value = decodeURIComponent(route.query.error)
  }
})

async function handleLogin() {
  loading.value = true
  error.value = ''
  
  const result = await authStore.login(email.value, password.value)
  
  if (result.success) {
    router.push('/films')
  } else {
    error.value = result.error
  }
  
  loading.value = false
}

async function handleGoogleAuth() {
  googleLoading.value = true
  error.value = ''
  
  try {
    const response = await api.get('/auth/google/redirect')
    if (response.data?.url) {
      window.location.href = response.data.url
    } else {
      error.value = 'Erro ao redirecionar para Google'
      googleLoading.value = false
    }
  } catch (err) {
    console.error('Erro ao iniciar autenticação Google:', err)
    error.value = 'Erro ao autenticar com Google. Tente novamente.'
    googleLoading.value = false
  }
}
</script>

<style scoped>
.login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: calc(100vh - 200px);
  padding: 2rem 1rem;
  background: linear-gradient(135deg, #e0e7ff 0%, #f3e8ff 100%);
  flex: 1;
}

.login-card {
  background: white;
  padding: 3rem;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  width: 100%;
  max-width: 400px;
}

.login-card h1 {
  text-align: center;
  margin-bottom: 0.5rem;
  color: #333;
}

.login-card p {
  text-align: center;
  color: #666;
  margin-bottom: 2rem;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-field {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-field label {
  font-weight: 500;
  color: #333;
}

.full-width {
  width: 100%;
}

.error-message {
  margin-top: 1rem;
  padding: 0.75rem;
  background: #fee;
  color: #c33;
  border-radius: 4px;
  text-align: center;
}

.links {
  margin-top: 1.5rem;
  text-align: center;
  color: #666;
}

.links a {
  color: #6366f1;
  text-decoration: none;
  font-weight: 500;
}

.links a:hover {
  text-decoration: underline;
}

.divider {
  display: flex;
  align-items: center;
  text-align: center;
  margin: 1.5rem 0;
  color: #999;
}

.divider::before,
.divider::after {
  content: '';
  flex: 1;
  border-bottom: 1px solid #e9ecef;
}

.divider span {
  padding: 0 1rem;
  font-size: 0.9rem;
}

.google-button {
  margin-top: 0;
}
</style>

