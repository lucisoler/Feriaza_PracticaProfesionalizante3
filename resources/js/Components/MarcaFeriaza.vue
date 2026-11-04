<template>
  <header class="fz-marca" :class="{ 'fz-marca-ancha': ancha }">
    <div class="fz-marca-fila">
      <div>
        <p class="fz-marca-nombre">Feriaza</p>
        <p class="fz-marca-lugar"><slot /></p>
      </div>
      <BotonModoOscuro />
    </div>
  </header>
</template>

<script setup>
import BotonModoOscuro from './BotonModoOscuro.vue'

defineProps({
  ancha: { type: Boolean, default: false },
})
</script>

<style scoped>
.fz-marca-fila {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
}
</style>
