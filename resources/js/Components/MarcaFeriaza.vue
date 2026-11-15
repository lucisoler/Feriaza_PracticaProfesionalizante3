<template>
  <header class="fz-marca" :class="{ 'fz-marca-ancha': ancha }">
    <div class="fz-marca-fila">
      <div>
        <p class="fz-marca-nombre">Feriaza</p>
        <p class="fz-marca-lugar"><slot /></p>
      </div>
      <div class="fz-marca-botones">
        <BotonMovimiento />
        <BotonModoOscuro />
      </div>
    </div>
  </header>
</template>

<script setup>
import BotonModoOscuro from './BotonModoOscuro.vue'
import BotonMovimiento from './BotonMovimiento.vue'

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

.fz-marca-botones {
  display: flex;
  flex-wrap: wrap;
  justify-content: flex-end;
  gap: 0.5rem;
}
</style>
