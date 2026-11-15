<template>
  <button
    type="button"
    class="fz-movimiento"
    :aria-pressed="activado ? 'true' : 'false'"
    :aria-label="activado ? 'Pausar animaciones' : 'Activar animaciones'"
    @click="alternar"
  >
    {{ activado ? '⏸ Pausar animaciones' : '▶ Activar animaciones' }}
  </button>
</template>

<script setup>
import { useMovimiento } from '../movimiento'

const { activado, alternar } = useMovimiento()
</script>

<style scoped>
.fz-movimiento {
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
