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
const googleLoading = ref(false)
const error = ref('')
const success = ref('')

async function handleRegister() {
  // Validar campos antes de enviar
  if (!form.value.name || !form.value.email || !form.value.password) {
    error.value = 'Por favor, preencha todos os campos obrigatórios.'
    return
  }

  if (form.value.password !== form.value.password_confirmation) {
    error.value = 'As senhas não coincidem.'
    return
  }

  if (form.value.password.length < 8) {
    error.value = 'A senha deve ter no mínimo 8 caracteres.'
    return
  }

  loading.value = true
  error.value = ''
  success.value = ''
  
  try {
    const response = await api.post('/register', form.value)
    
    if (response.data) {
      success.value = response.data.message || 'Conta criada com sucesso!'
      
      // Se tiver URL de verificação (desenvolvimento), mostrar
      if (response.data.verification_url) {
        success.value += ` Link: ${response.data.verification_url}`
      }
      
      // Redirecionar para login após 3 segundos
      setTimeout(() => {
        router.push('/login')
      }, 4000)
    }
  } catch (err) {
    // 422 é uma resposta de validação, não um erro do sistema
    // Não logar como erro no console para evitar poluição
    if (err.response?.status !== 422) {
      console.error('Erro ao registrar:', err)
    }
    
    if (err.response?.status === 500) {
      error.value = 'Erro interno do servidor. Verifique os logs ou tente novamente.'
    } else if (err.response?.status === 422 && err.response?.data?.errors) {
      // Erro de validação - exibir mensagens de forma amigável
      const errors = err.response.data.errors
      error.value = Object.values(errors).flat().join(', ')
    } else if (err.response?.data?.message) {
      error.value = err.response.data.message
    } else {
      error.value = 'Erro ao criar conta. Tente novamente.'
    }
  } finally {
    loading.value = false
  }
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
.register-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #e0e7ff 0%, #f3e8ff 100%);
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
  color: #6366f1;
  text-decoration: none;
  font-weight: 500;
}

.login-link a:hover {
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

/* Tablet */
@media (max-width: 1024px) {
  .register-container {
    padding: 1.5rem;
  }

  .register-card {
    padding: 2rem;
    max-width: 500px;
  }
}

/* Mobile */
@media (max-width: 640px) {
  .register-container {
    padding: 1rem;
  }

  .register-card {
    padding: 1.5rem;
    max-width: 100%;
  }

  .register-card h1 {
    font-size: 1.5rem;
  }

  .register-card p {
    font-size: 0.9rem;
  }

  .login-link {
    font-size: 0.9rem;
  }
}
</style>

