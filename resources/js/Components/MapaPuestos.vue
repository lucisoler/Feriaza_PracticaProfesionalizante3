<template>
  <div>
    <div class="fz-leyenda" aria-hidden="true">
      <span><i class="fz-mesa-libre"></i> Libre</span>
      <span><i class="fz-mesa-ocupada"></i> Ocupada</span>
      <span><i class="fz-mesa-elegida"></i> Tu mesa</span>
    </div>

    <!-- Plano fijo de mesas de la plaza (D-06) -->
    <div class="fz-mapa" role="group" aria-label="Mesas de la plaza">
      <button
        v-for="p in puestosOrdenados"
        :key="p.id"
        type="button"
        class="fz-mesa"
        :class="claseDe(p)"
        :disabled="!estaLibre(p)"
        :aria-pressed="seleccionado === p.id"
        :aria-label="estaLibre(p) ? `Mesa ${p.numero}, libre` : `Mesa ${p.numero}, ocupada`"
        @click="elegir(p)"
      >
        {{ p.numero }}
      </button>
    </div>

    <p v-if="puestos.length === 0" class="fz-ayuda" style="margin-top: 0.75rem">
      Todavía no hay mesas cargadas para este evento.
    </p>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  // Cada puesto: { id, numero, estado: 'libre' | 'reservado', habilitado? }
  puestos: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['seleccionar'])

const seleccionado = ref(null)

const puestosOrdenados = computed(() =>
  [...props.puestos].sort((a, b) => a.numero - b.numero)
)

// Una mesa se puede elegir si está libre y no fue retirada del plano
function estaLibre(p) {
  return p.estado === 'libre' && p.habilitado !== false
}

function claseDe(p) {
  if (seleccionado.value === p.id) return 'fz-mesa-elegida'
  return estaLibre(p) ? 'fz-mesa-libre' : 'fz-mesa-ocupada'
}

function elegir(p) {
  if (!estaLibre(p)) return
  seleccionado.value = p.id
  emit('seleccionar', p.id)
}
</script>
