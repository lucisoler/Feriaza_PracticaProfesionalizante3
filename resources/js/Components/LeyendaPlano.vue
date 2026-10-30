<template>
  <ul class="fz-leyenda" aria-label="Referencias del plano">
    <li v-for="c in categoriasVisibles" :key="c.id" :class="{ 'fz-leyenda-propia': c.id === resaltar }">
      <label v-if="editable" class="fz-leyenda-color" :title="`Cambiar el color de ${c.nombre}`">
        <input type="color" :value="c.color" @change="emit('cambiar-color', c, $event.target.value)" />
        <i :style="{ background: c.color, borderColor: c.color }"></i>
      </label>
      <i v-else :style="{ background: c.color, borderColor: c.color }"></i>
      {{ c.nombre }}
    </li>
    <li v-if="ocupada"><i class="fz-muestra-ocupada"></i> Ocupada</li>
    <li v-if="elegida"><i class="fz-muestra-elegida"></i> Tu mesa</li>
    <li><i class="fz-muestra-bloqueada"></i> No disponible</li>
  </ul>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  categorias: { type: Array, default: () => [] },
  // Para la vista del feriante: solo las categorías que aparecen en el plano
  soloIds: { type: Array, default: null },
  resaltar: { type: Number, default: null },
  ocupada: { type: Boolean, default: true },
  elegida: { type: Boolean, default: false },
  editable: { type: Boolean, default: false },
})

const emit = defineEmits(['cambiar-color'])

const categoriasVisibles = computed(() =>
  props.soloIds ? props.categorias.filter(c => props.soloIds.includes(c.id)) : props.categorias
)
</script>
