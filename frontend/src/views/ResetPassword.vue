<template>
  <div class="reset-password-container">
    <div class="reset-password-card">
      <h1>Redefinir Senha</h1>
      <p>Digite sua nova senha</p>
      
      <form @submit.prevent="handleResetPassword" class="reset-password-form">
        <div class="form-field">
          <label>Email</label>
          <InputText
            v-model="form.email"
            type="email"
            placeholder="seu@email.com"
            required
            class="full-width"
            :disabled="true"
          />
        </div>
        
        <div class="form-field">
          <label>Nova Senha *</label>
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
          <label>Confirmar Nova Senha *</label>
          <Password
            v-model="form.password_confirmation"
            placeholder="Confirme sua senha"
            toggleMask
            class="full-width"
            required
          />
        </div>
        
        <InputText
          v-model="form.token"
          type="hidden"
        />
        
        <Button
          type="submit"
          label="Redefinir Senha"
          icon="pi pi-key"
          :loading="loading"
          class="full-width"
        />
      </form>

      <div v-if="error" class="error-message">
        {{ error }}
      </div>

      <div v-if="success" class="success-message">
        <p>{{ success }}</p>
        <p class="success-detail">Você será redirecionado para o login...</p>
      </div>

      <div class="login-link">
        <p><router-link to="/login">Voltar ao login</router-link></p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import Button from 'primevue/button'
import api from '@/composables/useApi'

const router = useRouter()
const route = useRoute()

const form = ref({
  email: '',
  token: '',
  password: '',
  password_confirmation: ''
})

const loading = ref(false)
const error = ref('')
const success = ref('')

onMounted(() => {
  // Pegar token e email da URL
  form.value.token = route.query.token || ''
  form.value.email = route.query.email || ''
  
  if (!form.value.token || !form.value.email) {
    error.value = 'Link inválido ou expirado. Solicite um novo link de redefinição.'
  }
})

async function handleResetPassword() {
  loading.value = true
  error.value = ''
  success.value = ''
  
  try {
    const response = await api.post('/reset-password', form.value)
    
    if (response.data) {
      success.value = response.data.message || 'Senha redefinida com sucesso!'
      
      // Redirecionar para login após 2 segundos
      setTimeout(() => {
        router.push('/login')
      }, 2000)
    }
  } catch (err) {
    if (err.response?.data?.errors) {
      const errors = err.response.data.errors
      error.value = Object.values(errors).flat().join(', ')
    } else {
      error.value = err.response?.data?.message || 'Erro ao redefinir senha. Verifique se o link não expirou.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.reset-password-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #e0e7ff 0%, #f3e8ff 100%);
  padding: 2rem;
}

.reset-password-card {
  background: white;
  padding: 3rem;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  width: 100%;
  max-width: 450px;
}

.reset-password-card h1 {
  text-align: center;
  margin-bottom: 0.5rem;
  color: #333;
}

.reset-password-card p {
  text-align: center;
  color: #666;
  margin-bottom: 2rem;
}

.reset-password-form {
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
</style>

