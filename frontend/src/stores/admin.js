import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/composables/useApi'

export const useAdminStore = defineStore('admin', () => {
  const users = ref([])
  const roles = ref([])
  const permissions = ref([])
  const loading = ref(false)

  // Users
  async function fetchUsers() {
    loading.value = true
    try {
      const response = await api.get('/users')
      users.value = response.data.data || []
    } catch (error) {
      console.error('Erro ao buscar usuários:', error)
      users.value = []
    } finally {
      loading.value = false
    }
  }

  async function createUser(userData) {
    try {
      const response = await api.post('/users', userData)
      await fetchUsers()
      return { success: true, data: response.data.data }
    } catch (error) {
      return {
        success: false,
        error: error.response?.data?.message || 'Erro ao criar usuário'
      }
    }
  }

  async function updateUser(id, userData) {
    try {
      const response = await api.put(`/users/${id}`, userData)
      await fetchUsers()
      return { success: true, data: response.data.data }
    } catch (error) {
      return {
        success: false,
        error: error.response?.data?.message || 'Erro ao atualizar usuário'
      }
    }
  }

  async function deleteUser(id) {
    try {
      await api.delete(`/users/${id}`)
      await fetchUsers()
      return { success: true }
    } catch (error) {
      return {
        success: false,
        error: error.response?.data?.message || 'Erro ao deletar usuário'
      }
    }
  }

  // Roles
  async function fetchRoles() {
    try {
      const response = await api.get('/roles')
      // A API retorna { data: [...] } ou [...] diretamente
      const data = response.data?.data || response.data
      roles.value = Array.isArray(data) ? data : []
      console.log('Roles carregadas:', roles.value.length)
    } catch (error) {
      console.error('Erro ao buscar roles:', error)
      if (error.response) {
        console.error('Status:', error.response.status)
        console.error('Data:', error.response.data)
      }
      roles.value = []
      throw error
    }
  }

  async function createRole(roleData) {
    try {
      const response = await api.post('/roles', roleData)
      await fetchRoles()
      return { success: true, data: response.data.data }
    } catch (error) {
      return {
        success: false,
        error: error.response?.data?.message || 'Erro ao criar role'
      }
    }
  }

  async function updateRole(id, roleData) {
    try {
      const response = await api.put(`/roles/${id}`, roleData)
      await fetchRoles()
      return { success: true, data: response.data.data }
    } catch (error) {
      return {
        success: false,
        error: error.response?.data?.message || 'Erro ao atualizar role'
      }
    }
  }

  async function deleteRole(id) {
    try {
      await api.delete(`/roles/${id}`)
      await fetchRoles()
      return { success: true }
    } catch (error) {
      return {
        success: false,
        error: error.response?.data?.message || 'Erro ao deletar role'
      }
    }
  }

  // Permissions
  async function fetchPermissions() {
    try {
      const response = await api.get('/permissions')
      // A API retorna { data: [...] } ou [...] diretamente
      const data = response.data?.data || response.data
      permissions.value = Array.isArray(data) ? data : []
      console.log('Permissões carregadas:', permissions.value.length)
    } catch (error) {
      console.error('Erro ao buscar permissões:', error)
      if (error.response) {
        console.error('Status:', error.response.status)
        console.error('Data:', error.response.data)
      }
      permissions.value = []
      throw error
    }
  }

  async function createPermission(permissionData) {
    try {
      const response = await api.post('/permissions', permissionData)
      await fetchPermissions()
      return { success: true, data: response.data.data }
    } catch (error) {
      return {
        success: false,
        error: error.response?.data?.message || 'Erro ao criar permissão'
      }
    }
  }

  async function updatePermission(id, permissionData) {
    try {
      const response = await api.put(`/permissions/${id}`, permissionData)
      await fetchPermissions()
      return { success: true, data: response.data.data }
    } catch (error) {
      return {
        success: false,
        error: error.response?.data?.message || 'Erro ao atualizar permissão'
      }
    }
  }

  async function deletePermission(id) {
    try {
      await api.delete(`/permissions/${id}`)
      await fetchPermissions()
      return { success: true }
    } catch (error) {
      return {
        success: false,
        error: error.response?.data?.message || 'Erro ao deletar permissão'
      }
    }
  }

  return {
    users,
    roles,
    permissions,
    loading,
    fetchUsers,
    createUser,
    updateUser,
    deleteUser,
    fetchRoles,
    createRole,
    updateRole,
    deleteRole,
    fetchPermissions,
    createPermission,
    updatePermission,
    deletePermission
  }
})

