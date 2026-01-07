<template>
  <footer class="footer">
    <div class="footer-content">
      <p>&copy; {{ currentYear }} Cine Catálogo. Todos os direitos reservados.</p>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue'

const currentYear = computed(() => new Date().getFullYear())
</script>

<style scoped>
.footer {
  background: #ffffff;
  border-top: 1px solid #e9ecef;
  padding: 1.5rem 0;
  margin-top: auto;
}

.footer-content {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 2rem;
  text-align: center;
}

.footer-content p {
  margin: 0;
  color: #666;
  font-size: 0.9rem;
}

/* Tablet e Mobile */
@media (max-width: 1024px) {
  .footer-content {
    padding: 0 1rem;
  }
}

@media (max-width: 640px) {
  .footer {
    padding: 1rem 0;
  }

  .footer-content {
    padding: 0 1rem;
  }

  .footer-content p {
    font-size: 0.85rem;
  }
}
</style>

