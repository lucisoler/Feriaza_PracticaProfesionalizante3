<template>
  <div class="fz-mapa-satelital border border-borde rounded overflow-hidden bg-black">
    <iframe
      :src="url"
      :title="`Vista satelital de ${lugar.nombre}`"
      class="block w-full h-[420px] border-0"
      loading="lazy"
      referrerpolicy="no-referrer-when-downgrade"
      allowfullscreen
    />
    <p class="p-2 text-sm bg-white">
      {{ lugar.nombre }}: {{ lugar.direccion }}.
      <a :href="enlace" target="_blank" rel="noopener" class="underline">Abrir en Google Maps</a>
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

// Direcciones de cada lugar. El zoom es más cercano en la Plaza Central, que tiene calle exacta.
const LUGARES = {
  'plaza-central': {
    nombre: 'Plaza Central',
    direccion: 'Av. San Martín 500, H3705 Juan José Castelli, Chaco, Argentina',
    zoom: 18,
  },
  'pulmon-verde': {
    nombre: 'Pulmón Verde',
    direccion: 'H3705 Juan José Castelli, Chaco',
    zoom: 15,
  },
}

const props = defineProps({
  slug: { type: String, required: true },
})

const lugar = computed(() => LUGARES[props.slug] ?? LUGARES['plaza-central'])

// t=k: vista satelital. output=embed: se muestra dentro de un iframe sin clave de API.
const url = computed(() =>
  `https://maps.google.com/maps?q=${encodeURIComponent(lugar.value.direccion)}&t=k&z=${lugar.value.zoom}&output=embed`,
)

const enlace = computed(() =>
  `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(lugar.value.direccion)}&t=k`,
)
</script>
