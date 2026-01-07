<template>
  <div class="home">
    <h1>Cine Catálogo</h1>
    <p>Bem-vindo ao catálogo de filmes!</p>
  </div>
</template>

<script setup>
</script>

<style scoped>
.home {
  padding: 2rem;
  text-align: center;
}

/* Tablet */
@media (max-width: 1024px) {
  .home {
    padding: 1.5rem;
  }

  .home h1 {
    font-size: 2rem;
  }
}

/* Mobile */
@media (max-width: 640px) {
  .home {
    padding: 1rem;
  }

  .home h1 {
    font-size: 1.5rem;
  }

  .home p {
    font-size: 0.9rem;
  }
}
</style>

