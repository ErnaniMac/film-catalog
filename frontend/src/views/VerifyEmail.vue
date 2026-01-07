<template>
  <div class="verify-email-container">
    <div class="verify-email-card">
      <div v-if="loading" class="loading-state">
        <ProgressSpinner />
        <p>Verificando e-mail...</p>
      </div>
      
      <div v-else-if="success" class="success-state">
        <i class="pi pi-check-circle" style="font-size: 4rem; color: #4caf50;"></i>
        <h1>E-mail Verificado!</h1>
        <p>{{ success }}</p>
        <Button
          label="Ir para Login"
          icon="pi pi-sign-in"
          @click="$router.push('/login')"
        />
      </div>
      
      <div v-else-if="error" class="error-state">
        <i class="pi pi-times-circle" style="font-size: 4rem; color: #f44336;"></i>
        <h1>Erro na Verificação</h1>
        <p>{{ error }}</p>
        <Button
          label="Voltar ao Login"
          icon="pi pi-arrow-left"
          @click="$router.push('/login')"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Button from 'primevue/button'
import ProgressSpinner from 'primevue/progressspinner'
import api from '@/composables/useApi'

const route = useRoute()
const router = useRouter()

const loading = ref(true)
const success = ref('')
const error = ref('')

onMounted(async () => {
  try {
    const response = await api.get('/email/verify', {
      params: {
        id: route.query.id,
        hash: route.query.hash,
        expires: route.query.expires,
        signature: route.query.signature
      }
    })
    
    if (response.data) {
      success.value = response.data.message || 'E-mail verificado com sucesso!'
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Erro ao verificar e-mail. Link pode estar expirado ou inválido.'
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.verify-email-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #e0e7ff 0%, #f3e8ff 100%);
  padding: 2rem;
}

.verify-email-card {
  background: white;
  padding: 3rem;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  width: 100%;
  max-width: 500px;
  text-align: center;
}

.loading-state,
.success-state,
.error-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1.5rem;
}

.loading-state p {
  color: #666;
  margin-top: 1rem;
}

.success-state h1 {
  color: #4caf50;
  margin: 0;
}

.success-state p {
  color: #666;
}

.error-state h1 {
  color: #f44336;
  margin: 0;
}

.error-state p {
  color: #666;
}

/* Tablet */
@media (max-width: 1024px) {
  .verify-email-container {
    padding: 1.5rem;
  }

  .verify-email-card {
    padding: 2rem;
    max-width: 450px;
  }
}

/* Mobile */
@media (max-width: 640px) {
  .verify-email-container {
    padding: 1rem;
  }

  .verify-email-card {
    padding: 1.5rem;
    max-width: 100%;
  }

  .verify-email-card i {
    font-size: 3rem !important;
  }

  .success-state h1,
  .error-state h1 {
    font-size: 1.5rem;
  }

  .success-state p,
  .error-state p {
    font-size: 0.9rem;
  }

  .success-state :deep(.p-button),
  .error-state :deep(.p-button) {
    width: 100%;
  }
}
</style>

