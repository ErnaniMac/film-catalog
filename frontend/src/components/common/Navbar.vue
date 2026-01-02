<template>
  <nav class="navbar">
    <div class="navbar-content">
      <div class="navbar-brand">
        <router-link to="/films" class="brand-link">
          <h2>Catálogo de Filmes</h2>
        </router-link>
      </div>

      <div class="navbar-menu">
        <router-link to="/films" class="nav-link">Filmes</router-link>
        <router-link to="/favorites" class="nav-link">Favoritos</router-link>
        <router-link v-if="authStore.isAdmin" to="/admin" class="nav-link">Admin</router-link>
      </div>

      <div class="navbar-actions">
        <span v-if="authStore.user" class="user-info">
          {{ authStore.user.name }}
        </span>
        <Button
          label="Sair"
          icon="pi pi-sign-out"
          severity="danger"
          text
          @click="handleLogout"
        />
      </div>
    </div>
  </nav>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import Button from 'primevue/button'

const router = useRouter()
const authStore = useAuthStore()

async function handleLogout() {
  await authStore.logout()
  router.push('/login')
}
</script>

<style scoped>
.navbar {
  background: #fff;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  padding: 1rem 0;
  margin-bottom: 2rem;
}

.navbar-content {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.navbar-brand .brand-link {
  text-decoration: none;
  color: inherit;
}

.navbar-brand h2 {
  margin: 0;
  color: #1976d2;
}

.navbar-menu {
  display: flex;
  gap: 2rem;
}

.nav-link {
  text-decoration: none;
  color: #333;
  font-weight: 500;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  transition: background 0.2s;
}

.nav-link:hover {
  background: #f5f5f5;
}

.nav-link.router-link-active {
  color: #1976d2;
  background: #e3f2fd;
}

.navbar-actions {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-info {
  color: #666;
  font-weight: 500;
}
</style>

