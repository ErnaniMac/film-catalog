<template>
  <nav class="navbar">
    <div class="navbar-content">
      <div class="navbar-brand">
        <router-link to="/" class="brand-link">
          <img src="/favicon.svg" alt="Cine Catálogo" class="brand-logo" />
          <h2>Cine Catálogo</h2>
        </router-link>
      </div>

      <button class="mobile-menu-toggle" @click="toggleMobileMenu" aria-label="Toggle menu">
        <i :class="mobileMenuOpen ? 'pi pi-times' : 'pi pi-bars'"></i>
      </button>

      <div class="navbar-menu" :class="{ 'mobile-open': mobileMenuOpen }">
        <router-link to="/films" class="nav-link" @click="closeMobileMenu">Filmes</router-link>
        <router-link v-if="!authStore.isLoading && authStore.isAuthenticated && authStore.user && authStore.user.name" to="/favorites" class="nav-link" @click="closeMobileMenu">Favoritos</router-link>
        <router-link v-if="!authStore.isLoading && authStore.isAdmin" to="/admin" class="nav-link" @click="closeMobileMenu">Admin</router-link>
      </div>

      <div class="navbar-actions" :class="{ 'mobile-open': mobileMenuOpen }">
        <template v-if="!authStore.isLoading && authStore.isAuthenticated && authStore.user && authStore.user.name">
          <router-link to="/settings" class="user-info-link" @click="closeMobileMenu">
            <span class="user-info">
              {{ authStore.user.name }}
            </span>
          </router-link>
          <Button
            label="Sair"
            icon="pi pi-sign-out"
            severity="danger"
            text
            @click="handleLogout"
          />
        </template>
        <template v-else-if="!authStore.isLoading">
          <Button
            label="Login"
            icon="pi pi-sign-in"
            severity="secondary"
            outlined
            @click="handleLoginClick"
          />
          <Button
            label="Criar Conta"
            icon="pi pi-user-plus"
            @click="handleRegisterClick"
          />
        </template>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useFavoriteStore } from '@/stores/favorite'
import Button from 'primevue/button'

const router = useRouter()
const authStore = useAuthStore()
const favoriteStore = useFavoriteStore()
const mobileMenuOpen = ref(false)

function toggleMobileMenu() {
  mobileMenuOpen.value = !mobileMenuOpen.value
}

function closeMobileMenu() {
  mobileMenuOpen.value = false
}

async function handleLogout() {
  await authStore.logout()
  favoriteStore.clearFavorites()
  router.push('/films').then(() => {
    // Recarregar a página para garantir que os favoritos sejam atualizados
    window.location.reload()
  })
}

function handleLoginClick() {
  // Garantir que o estado está limpo antes de navegar
  if (authStore.token && !authStore.user) {
    authStore.clearAuth()
  }
  // Forçar navegação mesmo se houver problemas
  try {
    router.push('/login').catch((err) => {
      console.error('Erro ao navegar para login:', err)
      window.location.href = '/login'
    })
  } catch (error) {
    console.error('Erro ao navegar para login:', error)
    window.location.href = '/login'
  }
}

function handleRegisterClick() {
  // Garantir que o estado está limpo antes de navegar
  if (authStore.token && !authStore.user) {
    authStore.clearAuth()
  }
  // Forçar navegação mesmo se houver problemas
  try {
    router.push('/register').catch((err) => {
      console.error('Erro ao navegar para registro:', err)
      window.location.href = '/register'
    })
  } catch (error) {
    console.error('Erro ao navegar para registro:', error)
    window.location.href = '/register'
  }
}
</script>

<style scoped>
.navbar {
  background: #ffffff;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
  padding: 1rem 0;
  margin-bottom: 2rem;
  border-bottom: 1px solid #e9ecef;
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
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.brand-logo {
  width: 32px;
  height: 32px;
  flex-shrink: 0;
}

.navbar-brand h2 {
  margin: 0;
  color: #6366f1;
  font-weight: 600;
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
  background: #f1f5f9;
  color: #6366f1;
}

.nav-link.router-link-active {
  color: #6366f1;
  background: #eef2ff;
}

.navbar-actions {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-info-link {
  text-decoration: none;
  color: inherit;
  cursor: pointer;
  transition: opacity 0.2s;
}

.user-info-link:hover {
  opacity: 0.7;
}

.user-info {
  color: #666;
  font-weight: 500;
}

.mobile-menu-toggle {
  display: none;
  background: none;
  border: none;
  font-size: 1.5rem;
  color: #333;
  cursor: pointer;
  padding: 0.5rem;
  z-index: 1001;
}

.mobile-menu-toggle i {
  font-size: 1.5rem;
}

/* Tablet e Mobile */
@media (max-width: 1024px) {
  .navbar-content {
    padding: 0 1rem;
    flex-wrap: wrap;
    position: relative;
  }

  .mobile-menu-toggle {
    display: block;
  }

  .navbar-menu,
  .navbar-actions {
    display: none;
    width: 100%;
    flex-direction: column;
    gap: 0.5rem;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e9ecef;
  }

  .navbar-menu.mobile-open,
  .navbar-actions.mobile-open {
    display: flex;
  }

  .navbar-menu {
    order: 3;
  }

  .navbar-actions {
    order: 4;
  }

  .nav-link {
    width: 100%;
    padding: 0.75rem 1rem;
    text-align: left;
  }

  .user-info-link {
    width: 100%;
    padding: 0.75rem 1rem;
    text-align: left;
  }

  .navbar-actions :deep(.p-button) {
    width: 100%;
    justify-content: flex-start;
  }
}

/* Mobile pequeno */
@media (max-width: 640px) {
  .navbar-brand h2 {
    font-size: 1rem;
  }

  .brand-logo {
    width: 24px;
    height: 24px;
  }

  .navbar-content {
    padding: 0.75rem 1rem;
  }
}
</style>

