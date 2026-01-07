<template>
  <div class="admin-container">
    <div class="header">
      <h1>Administração</h1>
      <p>Gerencie usuários, roles e permissões</p>
    </div>

    <TabView>
      <TabPanel header="Usuários">
        <div class="table-header">
          <h2>Usuários</h2>
          <Button
            label="Novo Usuário"
            icon="pi pi-plus"
            @click="openUserDialog"
            class="add-button"
          />
        </div>

        <!-- Desktop Table -->
        <div class="desktop-view">
          <DataTable
            :value="adminStore.users"
            :loading="adminStore.loading"
            paginator
            :rows="10"
            :rowsPerPageOptions="[5, 10, 20]"
            stripedRows
          >
            <Column field="id" header="ID" sortable />
            <Column field="name" header="Nome" sortable />
            <Column field="email" header="Email" sortable />
            <Column header="Roles">
              <template #body="{ data }">
                <span
                  v-for="role in data.roles"
                  :key="role.id"
                  class="role-badge"
                >
                  {{ role.name }}
                </span>
              </template>
            </Column>
            <Column header="Ações">
              <template #body="{ data }">
                <Button
                  icon="pi pi-pencil"
                  severity="info"
                  text
                  rounded
                  @click="editUser(data)"
                />
                <Button
                  icon="pi pi-trash"
                  severity="danger"
                  text
                  rounded
                  @click="confirmDeleteUser(data)"
                />
              </template>
            </Column>
          </DataTable>
        </div>

        <!-- Mobile Cards -->
        <div class="mobile-view">
          <div v-if="adminStore.loading" class="loading">
            <ProgressSpinner />
            <p>Carregando usuários...</p>
          </div>
          <div v-else-if="adminStore.users.length === 0" class="no-data">
            <p>Nenhum usuário encontrado.</p>
          </div>
          <div v-else class="cards-container">
            <div
              v-for="user in adminStore.users"
              :key="user.id"
              class="admin-card"
            >
              <div class="card-header">
                <div class="card-id">ID: {{ user.id }}</div>
                <div class="card-actions">
                  <Button
                    icon="pi pi-pencil"
                    severity="info"
                    text
                    rounded
                    size="small"
                    @click="editUser(user)"
                    :title="'Editar'"
                  />
                  <Button
                    icon="pi pi-trash"
                    severity="danger"
                    text
                    rounded
                    size="small"
                    @click="confirmDeleteUser(user)"
                    :title="'Excluir'"
                  />
                </div>
              </div>
              <div class="card-content">
                <div class="card-field">
                  <span class="field-label">Nome:</span>
                  <span class="field-value">{{ user.name }}</span>
                </div>
                <div class="card-field">
                  <span class="field-label">Email:</span>
                  <span class="field-value">{{ user.email }}</span>
                </div>
                <div class="card-field">
                  <span class="field-label">Roles:</span>
                  <div class="field-value">
                    <span
                      v-for="role in user.roles"
                      :key="role.id"
                      class="role-badge"
                    >
                      {{ role.name }}
                    </span>
                    <span v-if="!user.roles || user.roles.length === 0" class="no-permissions">
                      Nenhuma
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </TabPanel>

      <TabPanel header="Roles">
        <div class="table-header">
          <h2>Roles</h2>
          <Button
            label="Nova Role"
            icon="pi pi-plus"
            @click="openRoleDialog"
            class="add-button"
          />
        </div>

        <!-- Desktop Table -->
        <div class="desktop-view">
          <div v-if="rolesLoading" class="loading">
            <ProgressSpinner />
            <p>Carregando roles...</p>
          </div>
          <div v-else-if="adminStore.roles.length === 0" class="no-data">
            <p>Nenhuma role encontrada.</p>
          </div>
          <DataTable
            v-else
            :value="adminStore.roles"
            paginator
            :rows="10"
            stripedRows
          >
            <Column field="id" header="ID" sortable />
            <Column field="name" header="Nome" sortable />
            <Column header="Permissões">
              <template #body="{ data }">
                <span
                  v-for="permission in data.permissions"
                  :key="permission.id"
                  class="permission-badge"
                >
                  {{ permission.name }}
                </span>
                <span v-if="!data.permissions || data.permissions.length === 0" class="no-permissions">
                  Sem permissões
                </span>
              </template>
            </Column>
            <Column header="Ações">
              <template #body="{ data }">
                <Button
                  icon="pi pi-pencil"
                  severity="info"
                  text
                  rounded
                  @click="editRole(data)"
                />
                <Button
                  icon="pi pi-trash"
                  severity="danger"
                  text
                  rounded
                  @click="confirmDeleteRole(data)"
                />
              </template>
            </Column>
          </DataTable>
        </div>

        <!-- Mobile Cards -->
        <div class="mobile-view">
          <div v-if="rolesLoading" class="loading">
            <ProgressSpinner />
            <p>Carregando roles...</p>
          </div>
          <div v-else-if="adminStore.roles.length === 0" class="no-data">
            <p>Nenhuma role encontrada.</p>
          </div>
          <div v-else class="cards-container">
            <div
              v-for="role in adminStore.roles"
              :key="role.id"
              class="admin-card"
            >
              <div class="card-header">
                <div class="card-id">ID: {{ role.id }}</div>
                <div class="card-actions">
                  <Button
                    icon="pi pi-pencil"
                    severity="info"
                    text
                    rounded
                    size="small"
                    @click="editRole(role)"
                    :title="'Editar'"
                  />
                  <Button
                    icon="pi pi-trash"
                    severity="danger"
                    text
                    rounded
                    size="small"
                    @click="confirmDeleteRole(role)"
                    :title="'Excluir'"
                  />
                </div>
              </div>
              <div class="card-content">
                <div class="card-field">
                  <span class="field-label">Nome:</span>
                  <span class="field-value">{{ role.name }}</span>
                </div>
                <div class="card-field">
                  <span class="field-label">Permissões:</span>
                  <div class="field-value">
                    <span
                      v-for="permission in role.permissions"
                      :key="permission.id"
                      class="permission-badge"
                    >
                      {{ permission.name }}
                    </span>
                    <span v-if="!role.permissions || role.permissions.length === 0" class="no-permissions">
                      Nenhuma
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </TabPanel>

      <TabPanel header="Permissões">
        <div class="table-header">
          <h2>Permissões</h2>
          <Button
            label="Nova Permissão"
            icon="pi pi-plus"
            @click="openPermissionDialog"
            class="add-button"
          />
        </div>

        <!-- Desktop Table -->
        <div class="desktop-view">
          <div v-if="permissionsLoading" class="loading">
            <ProgressSpinner />
            <p>Carregando permissões...</p>
          </div>
          <div v-else-if="adminStore.permissions.length === 0" class="no-data">
            <p>Nenhuma permissão encontrada.</p>
          </div>
          <DataTable
            v-else
            :value="adminStore.permissions"
            paginator
            :rows="10"
            stripedRows
          >
            <Column field="id" header="ID" sortable />
            <Column field="name" header="Nome" sortable />
            <Column header="Ações">
              <template #body="{ data }">
                <Button
                  icon="pi pi-pencil"
                  severity="info"
                  text
                  rounded
                  @click="editPermission(data)"
                />
                <Button
                  icon="pi pi-trash"
                  severity="danger"
                  text
                  rounded
                  @click="confirmDeletePermission(data)"
                />
              </template>
            </Column>
          </DataTable>
        </div>

        <!-- Mobile Cards -->
        <div class="mobile-view">
          <div v-if="permissionsLoading" class="loading">
            <ProgressSpinner />
            <p>Carregando permissões...</p>
          </div>
          <div v-else-if="adminStore.permissions.length === 0" class="no-data">
            <p>Nenhuma permissão encontrada.</p>
          </div>
          <div v-else class="cards-container">
            <div
              v-for="permission in adminStore.permissions"
              :key="permission.id"
              class="admin-card"
            >
              <div class="card-header">
                <div class="card-id">ID: {{ permission.id }}</div>
                <div class="card-actions">
                  <Button
                    icon="pi pi-pencil"
                    severity="info"
                    text
                    rounded
                    size="small"
                    @click="editPermission(permission)"
                    :title="'Editar'"
                  />
                  <Button
                    icon="pi pi-trash"
                    severity="danger"
                    text
                    rounded
                    size="small"
                    @click="confirmDeletePermission(permission)"
                    :title="'Excluir'"
                  />
                </div>
              </div>
              <div class="card-content">
                <div class="card-field">
                  <span class="field-label">Nome:</span>
                  <span class="field-value">{{ permission.name }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </TabPanel>
    </TabView>

    <!-- User Dialog -->
    <Dialog
      v-model:visible="userDialogVisible"
      :header="userDialogMode === 'create' ? 'Novo Usuário' : 'Editar Usuário'"
      :modal="true"
      :style="{ width: '500px' }"
    >
      <form @submit.prevent="saveUser">
        <div class="form-field">
          <label>Nome *</label>
          <InputText v-model="userForm.name" required />
        </div>
        <div class="form-field">
          <label>Email *</label>
          <InputText v-model="userForm.email" type="email" required />
        </div>
        <div class="form-field">
          <label>Senha {{ userDialogMode === 'create' ? '*' : '(deixe em branco para não alterar)' }}</label>
          <InputText v-model="userForm.password" type="password" :required="userDialogMode === 'create'" />
        </div>
        <div class="form-field">
          <label>Roles</label>
          <MultiSelect
            v-model="userForm.roles"
            :options="adminStore.roles"
            optionLabel="name"
            optionValue="name"
            placeholder="Selecione as roles"
          />
        </div>
        <div class="dialog-actions">
          <Button label="Cancelar" severity="secondary" @click="closeUserDialog" />
          <Button type="submit" :label="userDialogMode === 'create' ? 'Criar' : 'Salvar'" />
        </div>
      </form>
    </Dialog>

    <!-- Role Dialog -->
    <Dialog
      v-model:visible="roleDialogVisible"
      :header="roleDialogMode === 'create' ? 'Nova Role' : 'Editar Role'"
      :modal="true"
      :style="{ width: '500px' }"
    >
      <form @submit.prevent="saveRole">
        <div class="form-field">
          <label>Nome *</label>
          <InputText v-model="roleForm.name" required />
        </div>
        <div class="form-field">
          <label>Permissões</label>
          <MultiSelect
            v-model="roleForm.permissions"
            :options="adminStore.permissions"
            optionLabel="name"
            optionValue="name"
            placeholder="Selecione as permissões"
          />
        </div>
        <div class="dialog-actions">
          <Button label="Cancelar" severity="secondary" @click="closeRoleDialog" />
          <Button type="submit" :label="roleDialogMode === 'create' ? 'Criar' : 'Salvar'" />
        </div>
      </form>
    </Dialog>

    <!-- Permission Dialog -->
    <Dialog
      v-model:visible="permissionDialogVisible"
      :header="permissionDialogMode === 'create' ? 'Nova Permissão' : 'Editar Permissão'"
      :modal="true"
      :style="{ width: '400px' }"
    >
      <form @submit.prevent="savePermission">
        <div class="form-field">
          <label>Nome *</label>
          <InputText v-model="permissionForm.name" required />
        </div>
        <div class="dialog-actions">
          <Button label="Cancelar" severity="secondary" @click="closePermissionDialog" />
          <Button type="submit" :label="permissionDialogMode === 'create' ? 'Criar' : 'Salvar'" />
        </div>
      </form>
    </Dialog>

    <!-- Confirm Delete Dialog -->
    <Dialog
      v-model:visible="deleteDialogVisible"
      header="Confirmar Exclusão"
      :modal="true"
      :style="{ width: '400px' }"
    >
      <p>{{ deleteMessage }}</p>
      <template #footer>
        <Button label="Cancelar" severity="secondary" @click="deleteDialogVisible = false" />
        <Button label="Confirmar" severity="danger" @click="executeDelete" />
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAdminStore } from '@/stores/admin'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import MultiSelect from 'primevue/multiselect'
import TabView from 'primevue/tabview'
import TabPanel from 'primevue/tabpanel'
import ProgressSpinner from 'primevue/progressspinner'
import { useToast } from 'primevue/usetoast'

const adminStore = useAdminStore()
const toast = useToast()

const rolesLoading = ref(false)
const permissionsLoading = ref(false)

// User Dialog
const userDialogVisible = ref(false)
const userDialogMode = ref('create')
const userForm = ref({
  id: null,
  name: '',
  email: '',
  password: '',
  roles: []
})

// Role Dialog
const roleDialogVisible = ref(false)
const roleDialogMode = ref('create')
const roleForm = ref({
  id: null,
  name: '',
  permissions: []
})

// Permission Dialog
const permissionDialogVisible = ref(false)
const permissionDialogMode = ref('create')
const permissionForm = ref({
  id: null,
  name: ''
})

// Delete Dialog
const deleteDialogVisible = ref(false)
const deleteType = ref(null)
const deleteId = ref(null)
const deleteMessage = ref('')

onMounted(async () => {
  rolesLoading.value = true
  permissionsLoading.value = true
  
  try {
    await adminStore.fetchUsers()
    await adminStore.fetchRoles()
    await adminStore.fetchPermissions()
    
  } catch (error) {
    console.error('Erro ao carregar dados:', error)
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: 'Erro ao carregar dados. Verifique o console.',
      life: 5000
    })
  } finally {
    rolesLoading.value = false
    permissionsLoading.value = false
  }
})

// User functions
function openUserDialog() {
  userDialogMode.value = 'create'
  userForm.value = { id: null, name: '', email: '', password: '', roles: [] }
  userDialogVisible.value = true
}

function editUser(user) {
  userDialogMode.value = 'edit'
  userForm.value = {
    id: user.id,
    name: user.name,
    email: user.email,
    password: '',
    roles: user.roles.map(r => r.name)
  }
  userDialogVisible.value = true
}

function closeUserDialog() {
  userDialogVisible.value = false
  userForm.value = { id: null, name: '', email: '', password: '', roles: [] }
}

async function saveUser() {
  const data = {
    name: userForm.value.name,
    email: userForm.value.email,
    roles: userForm.value.roles
  }

  if (userForm.value.password) {
    data.password = userForm.value.password
  }

  const result = userDialogMode.value === 'create'
    ? await adminStore.createUser(data)
    : await adminStore.updateUser(userForm.value.id, data)

  if (result.success) {
    toast.add({
      severity: 'success',
      summary: 'Sucesso',
      detail: `Usuário ${userDialogMode.value === 'create' ? 'criado' : 'atualizado'} com sucesso!`,
      life: 3000
    })
    closeUserDialog()
  } else {
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: result.error,
      life: 3000
    })
  }
}

function confirmDeleteUser(user) {
  deleteType.value = 'user'
  deleteId.value = user.id
  deleteMessage.value = `Tem certeza que deseja excluir o usuário "${user.name}"?`
  deleteDialogVisible.value = true
}

// Role functions
function openRoleDialog() {
  roleDialogMode.value = 'create'
  roleForm.value = { id: null, name: '', permissions: [] }
  roleDialogVisible.value = true
}

function editRole(role) {
  roleDialogMode.value = 'edit'
  roleForm.value = {
    id: role.id,
    name: role.name,
    permissions: role.permissions.map(p => p.name)
  }
  roleDialogVisible.value = true
}

function closeRoleDialog() {
  roleDialogVisible.value = false
  roleForm.value = { id: null, name: '', permissions: [] }
}

async function saveRole() {
  const data = {
    name: roleForm.value.name,
    permissions: roleForm.value.permissions
  }

  const result = roleDialogMode.value === 'create'
    ? await adminStore.createRole(data)
    : await adminStore.updateRole(roleForm.value.id, data)

  if (result.success) {
    toast.add({
      severity: 'success',
      summary: 'Sucesso',
      detail: `Role ${roleDialogMode.value === 'create' ? 'criada' : 'atualizada'} com sucesso!`,
      life: 3000
    })
    closeRoleDialog()
  } else {
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: result.error,
      life: 3000
    })
  }
}

function confirmDeleteRole(role) {
  deleteType.value = 'role'
  deleteId.value = role.id
  deleteMessage.value = `Tem certeza que deseja excluir a role "${role.name}"?`
  deleteDialogVisible.value = true
}

// Permission functions
function openPermissionDialog() {
  permissionDialogMode.value = 'create'
  permissionForm.value = { id: null, name: '' }
  permissionDialogVisible.value = true
}

function editPermission(permission) {
  permissionDialogMode.value = 'edit'
  permissionForm.value = {
    id: permission.id,
    name: permission.name
  }
  permissionDialogVisible.value = true
}

function closePermissionDialog() {
  permissionDialogVisible.value = false
  permissionForm.value = { id: null, name: '' }
}

async function savePermission() {
  const data = { name: permissionForm.value.name }

  const result = permissionDialogMode.value === 'create'
    ? await adminStore.createPermission(data)
    : await adminStore.updatePermission(permissionForm.value.id, data)

  if (result.success) {
    toast.add({
      severity: 'success',
      summary: 'Sucesso',
      detail: `Permissão ${permissionDialogMode.value === 'create' ? 'criada' : 'atualizada'} com sucesso!`,
      life: 3000
    })
    closePermissionDialog()
  } else {
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: result.error,
      life: 3000
    })
  }
}

function confirmDeletePermission(permission) {
  deleteType.value = 'permission'
  deleteId.value = permission.id
  deleteMessage.value = `Tem certeza que deseja excluir a permissão "${permission.name}"?`
  deleteDialogVisible.value = true
}

// Delete function
async function executeDelete() {
  let result

  if (deleteType.value === 'user') {
    result = await adminStore.deleteUser(deleteId.value)
  } else if (deleteType.value === 'role') {
    result = await adminStore.deleteRole(deleteId.value)
  } else if (deleteType.value === 'permission') {
    result = await adminStore.deletePermission(deleteId.value)
  }

  if (result?.success) {
    toast.add({
      severity: 'success',
      summary: 'Sucesso',
      detail: 'Item excluído com sucesso!',
      life: 3000
    })
  } else {
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: result?.error || 'Erro ao excluir',
      life: 3000
    })
  }

  deleteDialogVisible.value = false
}
</script>

<style scoped>
.admin-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem;
  width: 100%;
  box-sizing: border-box;
}

.header {
  text-align: center;
  margin-bottom: 2rem;
}

.header h1 {
  font-size: 2.5rem;
  margin-bottom: 0.5rem;
}

.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.table-header h2 {
  margin: 0;
}

.role-badge,
.permission-badge {
  display: inline-block;
  background: #eef2ff;
  color: #6366f1;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.85rem;
  margin-right: 0.5rem;
  margin-bottom: 0.25rem;
}

.form-field {
  margin-bottom: 1.5rem;
}

.form-field label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
}

.form-field input,
.form-field .p-multiselect {
  width: 100%;
}

.dialog-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  margin-top: 1.5rem;
}

.loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem;
  gap: 1rem;
}

.no-data {
  text-align: center;
  padding: 4rem;
  color: #666;
}

.no-permissions {
  color: #999;
  font-style: italic;
}

/* Desktop e Mobile Views */
.desktop-view {
  display: block;
}

.mobile-view {
  display: none;
}

/* Cards Mobile */
.cards-container {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.admin-card {
  background: white;
  border-radius: 8px;
  padding: 1rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
  border: 1px solid #e9ecef;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid #e9ecef;
}

.card-id {
  font-size: 0.85rem;
  color: #666;
  font-weight: 600;
}

.card-actions {
  display: flex;
  gap: 0.5rem;
}

.card-content {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.card-field {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.field-label {
  font-size: 0.75rem;
  color: #666;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.field-value {
  font-size: 0.9rem;
  color: #1e293b;
  word-break: break-word;
}

.field-value .role-badge,
.field-value .permission-badge {
  display: inline-block;
  margin-right: 0.5rem;
  margin-bottom: 0.25rem;
}

/* Estilos para TabView e tabelas */
:deep(.p-tabview) {
  width: 100%;
  box-sizing: border-box;
}

:deep(.p-tabview .p-tabview-panels) {
  padding: 1.5rem;
  min-height: 500px;
  box-sizing: border-box;
}

:deep(.p-tabview .p-tabview-panel) {
  width: 100%;
  box-sizing: border-box;
  overflow-x: hidden;
}

/* Tamanho padrão grande para todas as tabelas */
:deep(.p-datatable) {
  width: 100%;
  min-width: 1200px;
}

:deep(.p-datatable-wrapper) {
  width: 100%;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  position: relative;
}

/* Tablet */
@media (max-width: 1024px) {
  .admin-container {
    padding: 1.5rem;
  }

  .header h1 {
    font-size: 2rem;
  }

  .table-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }

  .table-header :deep(.p-button) {
    width: 100%;
  }

  :deep(.p-datatable) {
    min-width: 800px;
  }

  :deep(.p-dialog) {
    width: 90% !important;
    max-width: 600px;
  }

  .form-field {
    margin-bottom: 1rem;
  }

  .dialog-actions {
    flex-direction: column;
  }

  .dialog-actions :deep(.p-button) {
    width: 100%;
  }
}

/* Mobile */
@media (max-width: 640px) {
  .admin-container {
    padding: 0.5rem;
    overflow-x: hidden;
  }

  .header {
    margin-bottom: 1rem;
  }

  .header h1 {
    font-size: 1.5rem;
  }

  .header p {
    font-size: 0.85rem;
  }

  .table-header {
    flex-direction: column;
    align-items: stretch;
    gap: 0.75rem;
    margin-bottom: 1rem;
  }

  .table-header h2 {
    font-size: 1.1rem;
  }

  .add-button {
    width: 100%;
    font-size: 0.9rem;
    padding: 0.75rem;
  }

  /* Esconder desktop, mostrar mobile */
  .desktop-view {
    display: none !important;
  }

  .mobile-view {
    display: block !important;
  }

  :deep(.p-tabview) {
    width: 100%;
    overflow-x: hidden;
  }

  :deep(.p-tabview .p-tabview-nav) {
    flex-wrap: nowrap;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
  }

  :deep(.p-tabview .p-tabview-nav li) {
    flex-shrink: 0;
  }

  :deep(.p-tabview .p-tabview-nav li .p-tabview-nav-link) {
    padding: 0.75rem 1rem;
    font-size: 0.85rem;
    white-space: nowrap;
  }

  :deep(.p-tabview .p-tabview-panels) {
    padding: 0.5rem 0;
    min-height: 300px;
    overflow-x: hidden;
  }

  .cards-container {
    gap: 0.75rem;
  }

  .admin-card {
    padding: 1rem;
    border-radius: 8px;
  }

  .card-header {
    margin-bottom: 0.75rem;
    padding-bottom: 0.5rem;
  }

  .card-id {
    font-size: 0.8rem;
  }

  .card-actions :deep(.p-button) {
    padding: 0.5rem;
    width: 2.5rem;
    height: 2.5rem;
  }

  .card-field {
    gap: 0.3rem;
  }

  .field-label {
    font-size: 0.7rem;
  }

  .field-value {
    font-size: 0.85rem;
  }

  .role-badge,
  .permission-badge {
    font-size: 0.7rem;
    padding: 0.2rem 0.4rem;
    margin-right: 0.3rem;
    margin-bottom: 0.3rem;
  }

  .loading,
  .no-data {
    padding: 2rem 1rem;
  }

  .loading p,
  .no-data p {
    font-size: 0.9rem;
  }

  :deep(.p-dialog) {
    width: 95% !important;
    max-width: none;
    margin: 0.5rem !important;
  }

  :deep(.p-dialog-header) {
    padding: 0.75rem;
  }

  :deep(.p-dialog-title) {
    font-size: 1.1rem;
  }

  :deep(.p-dialog-content) {
    padding: 0.75rem;
    max-height: calc(100vh - 200px);
    overflow-y: auto;
  }

  .form-field {
    margin-bottom: 0.75rem;
  }

  .form-field label {
    font-size: 0.85rem;
    margin-bottom: 0.3rem;
  }

  .dialog-actions {
    flex-direction: column;
    gap: 0.5rem;
  }

  .dialog-actions :deep(.p-button) {
    width: 100%;
    font-size: 0.85rem;
  }

  :deep(.p-multiselect) {
    width: 100%;
  }
}
</style>
