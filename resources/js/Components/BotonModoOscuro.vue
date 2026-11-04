<template>
  <button type="button" class="fz-modo-oscuro" @click="alternar" :aria-label="tema === 'oscuro' ? 'Pasar a modo claro' : 'Pasar a modo oscuro'">
    {{ tema === 'oscuro' ? '☀️ Modo claro' : '🌙 Modo oscuro' }}
  </button>
</template>

<script setup>
import { useModoOscuro } from '../modoOscuro'

const { tema, alternar } = useModoOscuro()
</script>

<style scoped>
.fz-modo-oscuro {
  background: none;
  border: 1px solid var(--color-borde);
  border-radius: 4px;
  padding: 0.35rem 0.7rem;
  font: inherit;
  font-size: 0.9rem;
  color: var(--color-noche);
  cursor: pointer;
}
</style>
