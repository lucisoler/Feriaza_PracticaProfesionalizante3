<template>
  <p v-if="!conectado" class="fz-sin-conexion" role="status">
    Estás sin conexión a internet. Lo que hagas ahora podría no guardarse.
  </p>
</template>

<script setup>
import { onMounted, onUnmounted, ref } from 'vue'

const conectado = ref(navigator.onLine)

function actualizar() {
  conectado.value = navigator.onLine
}

onMounted(() => {
  window.addEventListener('online', actualizar)
  window.addEventListener('offline', actualizar)
})

onUnmounted(() => {
  window.removeEventListener('online', actualizar)
  window.removeEventListener('offline', actualizar)
})
</script>

<style scoped>
.fz-sin-conexion {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 100;
  margin: 0;
  padding: 0.5rem 1rem;
  text-align: center;
  font-size: 0.9rem;
  font-weight: 700;
  background: var(--color-error);
  color: #fff;
}
</style>
