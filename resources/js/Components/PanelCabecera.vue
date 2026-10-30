<template>
  <header class="fz-panel-cabecera">
    <div>
      <p class="fz-marca-nombre">Feriaza</p>
      <p class="fz-marca-lugar">Panel del coordinador</p>
    </div>
    <nav class="fz-nav" aria-label="Secciones del panel">
      <Link v-for="item in items" :key="item.href" :href="item.href" class="fz-nav-item"
        :class="{ 'fz-nav-actual': actual === item.clave }" :aria-current="actual === item.clave ? 'page' : null">
        {{ item.texto }}
      </Link>
    </nav>
    <div class="fz-panel-acciones">
      <slot />
      <button type="button" class="fz-enlace" @click="router.post('/logout')">Cerrar sesión</button>
    </div>
  </header>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'

defineProps({
  // feriantes | eventos | plano
  actual: { type: String, required: true },
})

const items = [
  { clave: 'feriantes', texto: 'Feriantes', href: '/coordinador' },
  { clave: 'eventos', texto: 'Eventos', href: '/coordinador/eventos' },
  { clave: 'plano', texto: 'Plano de la feria', href: '/coordinador/plano' },
]
</script>
