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
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'

const router = useRouter()
const authStore = useAuthStore()

const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')

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
</style>

