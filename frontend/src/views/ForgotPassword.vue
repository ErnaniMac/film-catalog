<template>
  <div class="forgot-password-container">
    <div class="forgot-password-card">
      <h1>Recuperar Senha</h1>
      <p>Digite seu e-mail para receber o link de redefinição</p>
      
      <form @submit.prevent="handleSendResetLink" class="forgot-password-form">
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
        
        <Button
          type="submit"
          label="Enviar Link de Redefinição"
          icon="pi pi-envelope"
          :loading="loading"
          class="full-width"
        />
      </form>

      <div v-if="error" class="error-message">
        {{ error }}
      </div>

      <div v-if="success" class="success-message">
        {{ success }}
      </div>

      <div class="login-link">
        <p>Lembrou sua senha? <router-link to="/login">Voltar ao login</router-link></p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import api from '@/composables/useApi'

const email = ref('')
const loading = ref(false)
const error = ref('')
const success = ref('')

async function handleSendResetLink() {
  // Validar email antes de enviar
  if (!email.value || !email.value.includes('@')) {
    error.value = 'Por favor, insira um e-mail válido.'
    return
  }

  loading.value = true
  error.value = ''
  success.value = ''
  
  try {
    const response = await api.post('/forgot-password', {
      email: email.value
    })
    
    if (response.data) {
      success.value = response.data.message || 'Link de redefinição enviado com sucesso!'
      email.value = ''
    }
  } catch (err) {
    console.error('Erro ao enviar link de recuperação:', err)
    
    // Tratar erro específico de método não permitido
    if (err.response?.status === 405) {
      error.value = 'Erro na requisição. Por favor, tente novamente.'
    } else if (err.response?.data?.errors) {
      const errors = err.response.data.errors
      error.value = Object.values(errors).flat().join(', ')
    } else {
      error.value = err.response?.data?.message || 'Erro ao enviar link. Verifique se o e-mail está correto.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.forgot-password-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 2rem;
}

.forgot-password-card {
  background: white;
  padding: 3rem;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  width: 100%;
  max-width: 450px;
}

.forgot-password-card h1 {
  text-align: center;
  margin-bottom: 0.5rem;
  color: #333;
}

.forgot-password-card p {
  text-align: center;
  color: #666;
  margin-bottom: 2rem;
}

.forgot-password-form {
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
  padding: 0.75rem;
  background: #efe;
  color: #3c3;
  border-radius: 4px;
  text-align: center;
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

/* Tablet */
@media (max-width: 1024px) {
  .forgot-password-container {
    padding: 1.5rem;
  }

  .forgot-password-card {
    padding: 2rem;
    max-width: 450px;
  }
}

/* Mobile */
@media (max-width: 640px) {
  .forgot-password-container {
    padding: 1rem;
  }

  .forgot-password-card {
    padding: 1.5rem;
    max-width: 100%;
  }

  .forgot-password-card h1 {
    font-size: 1.5rem;
  }

  .forgot-password-card p {
    font-size: 0.9rem;
  }
}
</style>

