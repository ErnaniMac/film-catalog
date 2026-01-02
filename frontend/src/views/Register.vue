<template>
  <div class="register-container">
    <div class="register-card">
      <h1>Cadastro</h1>
      <p>Crie sua conta no Catálogo de Filmes</p>
      
      <form @submit.prevent="handleRegister" class="register-form">
        <div class="form-field">
          <label>Nome *</label>
          <InputText
            v-model="form.name"
            type="text"
            placeholder="Seu nome completo"
            required
            class="full-width"
          />
        </div>
        
        <div class="form-field">
          <label>Email *</label>
          <InputText
            v-model="form.email"
            type="email"
            placeholder="seu@email.com"
            required
            class="full-width"
          />
        </div>
        
        <div class="form-field">
          <label>Senha *</label>
          <Password
            v-model="form.password"
            placeholder="Mínimo 8 caracteres"
            :feedback="true"
            toggleMask
            class="full-width"
            required
          />
        </div>
        
        <div class="form-field">
          <label>Confirmar Senha *</label>
          <Password
            v-model="form.password_confirmation"
            placeholder="Confirme sua senha"
            toggleMask
            class="full-width"
            required
          />
        </div>
        
        <Button
          type="submit"
          label="Cadastrar"
          icon="pi pi-user-plus"
          :loading="loading"
          class="full-width"
        />
      </form>

      <div v-if="error" class="error-message">
        {{ error }}
      </div>

      <div v-if="success" class="success-message">
        <p>{{ success }}</p>
        <p class="success-detail">Verifique seu e-mail para ativar sua conta.</p>
      </div>

      <div class="login-link">
        <p>Já tem uma conta? <router-link to="/login">Faça login</router-link></p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import Button from 'primevue/button'
import api from '@/composables/useApi'

const router = useRouter()

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
})

const loading = ref(false)
const error = ref('')
const success = ref('')

async function handleRegister() {
  loading.value = true
  error.value = ''
  success.value = ''
  
  try {
    const response = await api.post('/register', form.value)
    
    if (response.data) {
      success.value = response.data.message || 'Conta criada com sucesso!'
      
      // Redirecionar para login após 3 segundos
      setTimeout(() => {
        router.push('/login')
      }, 3000)
    }
  } catch (err) {
    if (err.response?.data?.errors) {
      const errors = err.response.data.errors
      error.value = Object.values(errors).flat().join(', ')
    } else {
      error.value = err.response?.data?.message || 'Erro ao criar conta. Tente novamente.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.register-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 2rem;
}

.register-card {
  background: white;
  padding: 3rem;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  width: 100%;
  max-width: 450px;
}

.register-card h1 {
  text-align: center;
  margin-bottom: 0.5rem;
  color: #333;
}

.register-card p {
  text-align: center;
  color: #666;
  margin-bottom: 2rem;
}

.register-form {
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

.success-message {
  margin-top: 1rem;
  padding: 1rem;
  background: #efe;
  color: #3c3;
  border-radius: 4px;
  text-align: center;
}

.success-detail {
  margin-top: 0.5rem;
  font-size: 0.9rem;
  color: #666;
}

.login-link {
  margin-top: 1.5rem;
  text-align: center;
  color: #666;
}

.login-link a {
  color: #1976d2;
  text-decoration: none;
  font-weight: 500;
}

.login-link a:hover {
  text-decoration: underline;
}
</style>

