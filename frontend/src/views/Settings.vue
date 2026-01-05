<template>
  <div class="settings-container">
    <div class="header">
      <h1>Configurações da Conta</h1>
    </div>

    <div v-if="authStore.user" class="settings-content">
      <div class="settings-card">
        <h2>Informações Pessoais</h2>
        <div class="info-item">
          <label>Nome:</label>
          <span>{{ authStore.user.name }}</span>
        </div>
        <div class="info-item">
          <label>E-mail:</label>
          <span>{{ authStore.user.email }}</span>
        </div>
        <div v-if="authStore.user.email_verified_at" class="info-item">
          <label>E-mail verificado:</label>
          <span class="verified">✓ Verificado</span>
        </div>
        <div v-else class="info-item">
          <label>E-mail verificado:</label>
          <span class="not-verified">✗ Não verificado</span>
        </div>
      </div>

      <div class="settings-card">
        <h2>Ações</h2>
        <div class="actions">
          <Button
            label="Alterar Nome"
            icon="pi pi-user-edit"
            severity="secondary"
            outlined
            @click="showChangeName = true"
          />
          <Button
            label="Alterar Senha"
            icon="pi pi-key"
            severity="secondary"
            outlined
            @click="showChangePassword = true"
          />
          <Button
            label="Sair da Conta"
            icon="pi pi-sign-out"
            severity="danger"
            outlined
            @click="handleLogout"
          />
        </div>
      </div>
    </div>

    <div v-else class="no-user">
      <p>Usuário não encontrado.</p>
      <Button
        label="Voltar"
        icon="pi pi-arrow-left"
        @click="$router.push('/films')"
      />
    </div>

    <!-- Modal Alterar Nome -->
    <Dialog
      v-model:visible="showChangeName"
      header="Alterar Nome"
      :modal="true"
      :style="{ width: '500px' }"
      :closable="true"
      :dismissableMask="true"
      @hide="resetNameForm"
    >
      <div class="form-group">
        <label for="new-name">Novo Nome:</label>
        <InputText
          id="new-name"
          v-model="newName"
          placeholder="Digite seu novo nome"
          class="form-input"
        />
      </div>
      <template #footer>
        <Button
          label="Cancelar"
          severity="secondary"
          outlined
          @click="showChangeName = false"
        />
        <Button
          label="Salvar"
          :loading="updatingName"
          @click="handleUpdateName"
        />
      </template>
    </Dialog>

    <!-- Modal Alterar Senha -->
    <Dialog
      v-model:visible="showChangePassword"
      header="Alterar Senha"
      :modal="true"
      :style="{ width: '500px' }"
      :closable="true"
      :dismissableMask="true"
      @hide="resetPasswordForm"
    >
      <div class="form-group">
        <label for="current-password">Senha Atual:</label>
        <Password
          id="current-password"
          v-model="currentPassword"
          placeholder="Digite sua senha atual"
          :feedback="false"
          toggleMask
          class="form-input"
        />
      </div>
      <div class="form-group">
        <label for="new-password">Nova Senha:</label>
        <Password
          id="new-password"
          v-model="newPassword"
          placeholder="Digite sua nova senha"
          :feedback="true"
          toggleMask
          class="form-input"
        />
      </div>
      <div class="form-group">
        <label for="confirm-password">Confirmar Nova Senha:</label>
        <Password
          id="confirm-password"
          v-model="confirmPassword"
          placeholder="Confirme sua nova senha"
          :feedback="false"
          toggleMask
          class="form-input"
        />
      </div>
      <template #footer>
        <Button
          label="Cancelar"
          severity="secondary"
          outlined
          @click="showChangePassword = false"
        />
        <Button
          label="Salvar"
          :loading="updatingPassword"
          @click="handleUpdatePassword"
        />
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import { useToast } from 'primevue/usetoast'
import api from '@/composables/useApi'

const router = useRouter()
const authStore = useAuthStore()
const toast = useToast()

const showChangeName = ref(false)
const showChangePassword = ref(false)
const newName = ref('')
const currentPassword = ref('')
const newPassword = ref('')
const confirmPassword = ref('')
const updatingName = ref(false)
const updatingPassword = ref(false)

onMounted(async () => {
  if (!authStore.user && authStore.token) {
    await authStore.fetchUser()
  }
  if (!authStore.isAuthenticated) {
    router.push('/login')
  }
  if (authStore.user) {
    newName.value = authStore.user.name
  }
})

async function handleUpdateName() {
  if (!newName.value || newName.value.trim() === '') {
    toast.add({
      severity: 'warn',
      summary: 'Atenção',
      detail: 'O nome não pode estar vazio',
      life: 3000
    })
    return
  }

  updatingName.value = true
  try {
    const response = await api.put('/user/profile', {
      name: newName.value.trim()
    })
    
    // Atualizar usuário no store
    authStore.user.name = newName.value.trim()
    
    toast.add({
      severity: 'success',
      summary: 'Sucesso',
      detail: 'Nome atualizado com sucesso!',
      life: 3000
    })
    
    showChangeName.value = false
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: error.response?.data?.message || 'Erro ao atualizar nome',
      life: 3000
    })
  } finally {
    updatingName.value = false
  }
}

async function handleUpdatePassword() {
  if (!currentPassword.value || !newPassword.value || !confirmPassword.value) {
    toast.add({
      severity: 'warn',
      summary: 'Atenção',
      detail: 'Preencha todos os campos',
      life: 3000
    })
    return
  }

  if (newPassword.value !== confirmPassword.value) {
    toast.add({
      severity: 'warn',
      summary: 'Atenção',
      detail: 'As senhas não coincidem',
      life: 3000
    })
    return
  }

  if (newPassword.value.length < 8) {
    toast.add({
      severity: 'warn',
      summary: 'Atenção',
      detail: 'A senha deve ter no mínimo 8 caracteres',
      life: 3000
    })
    return
  }

  updatingPassword.value = true
  try {
    await api.put('/user/password', {
      current_password: currentPassword.value,
      password: newPassword.value,
      password_confirmation: confirmPassword.value
    })
    
    toast.add({
      severity: 'success',
      summary: 'Sucesso',
      detail: 'Senha atualizada com sucesso!',
      life: 3000
    })
    
    // Limpar campos
    currentPassword.value = ''
    newPassword.value = ''
    confirmPassword.value = ''
    showChangePassword.value = false
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: error.response?.data?.message || 'Erro ao atualizar senha',
      life: 3000
    })
  } finally {
    updatingPassword.value = false
  }
}

function resetNameForm() {
  if (authStore.user) {
    newName.value = authStore.user.name
  }
}

function resetPasswordForm() {
  currentPassword.value = ''
  newPassword.value = ''
  confirmPassword.value = ''
}

async function handleLogout() {
  await authStore.logout()
  router.push('/login')
}
</script>

<style scoped>
.settings-container {
  max-width: 800px;
  margin: 0 auto;
  padding: 2rem;
}

.header {
  text-align: center;
  margin-bottom: 2rem;
}

.header h1 {
  font-size: 2rem;
  color: #1e293b;
  font-weight: 600;
}

.loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem;
  gap: 1rem;
}

.settings-content {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.settings-card {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
  border: 1px solid #e9ecef;
}

.settings-card h2 {
  font-size: 1.5rem;
  color: #1e293b;
  margin-bottom: 1.5rem;
  font-weight: 600;
}

.info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 0;
  border-bottom: 1px solid #e9ecef;
}

.info-item:last-child {
  border-bottom: none;
}

.info-item label {
  font-weight: 500;
  color: #475569;
}

.info-item span {
  color: #1e293b;
}

.verified {
  color: #10b981;
  font-weight: 500;
}

.not-verified {
  color: #ef4444;
  font-weight: 500;
}

.actions {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
}

.form-group label {
  font-weight: 500;
  color: #475569;
  font-size: 0.95rem;
}

.form-input {
  width: 100%;
}

.no-user {
  text-align: center;
  padding: 4rem;
  color: #666;
}
</style>

