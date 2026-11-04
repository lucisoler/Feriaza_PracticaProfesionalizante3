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
      <div class="fz-avisos">
        <button type="button" class="fz-avisos-boton" :aria-expanded="abierto" @click="abierto = !abierto">
          Avisos
          <span v-if="avisos.length" class="fz-avisos-contador">{{ avisos.length }}</span>
        </button>
        <div v-if="abierto" class="fz-avisos-lista" role="menu">
          <p v-if="avisos.length === 0" class="fz-secundario" style="margin: 0; padding: 0.75rem">Sin avisos nuevos.</p>
          <div v-for="a in avisos" :key="a.id" class="fz-avisos-item">
            <p style="margin: 0">{{ a.mensaje }}</p>
            <button type="button" class="fz-enlace" @click="marcarLeido(a)">Visto</button>
          </div>
        </div>
      </div>
      <BotonModoOscuro />
      <slot />
      <button type="button" class="fz-enlace" @click="router.post('/logout')">Cerrar sesión</button>
    </div>
  </header>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import BotonModoOscuro from './BotonModoOscuro.vue'

defineProps({
  // feriantes | eventos | plano
  actual: { type: String, required: true },
})

const items = [
  { clave: 'feriantes', texto: 'Feriantes', href: '/coordinador' },
  { clave: 'eventos', texto: 'Eventos', href: '/coordinador/eventos' },
  { clave: 'plano', texto: 'Plano de la feria', href: '/coordinador/plano' },
]

const avisos = computed(() => usePage().props.avisos ?? [])
const abierto = ref(false)

function marcarLeido(aviso) {
  router.patch(`/coordinador/avisos/${aviso.id}/leido`, {}, { preserveScroll: true, preserveState: true })
}

// Para que un aviso nuevo (ej.: un feriante cedió su lugar) aparezca sin recargar la página
let intervalo = null
onMounted(() => {
  intervalo = setInterval(() => {
    router.reload({ only: ['avisos'], preserveScroll: true, preserveState: true })
  }, 15000)
})
onUnmounted(() => clearInterval(intervalo))
</script>

<style scoped>
.fz-avisos {
  position: relative;
}

.fz-avisos-boton {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  background: none;
  border: 1px solid var(--color-borde);
  border-radius: 4px;
  padding: 0.35rem 0.7rem;
  font-size: 0.9rem;
  color: var(--color-noche);
  cursor: pointer;
}

.fz-avisos-contador {
  background: var(--color-quebracho);
  color: #fff;
  font-size: 0.75rem;
  line-height: 1;
  padding: 0.15rem 0.4rem;
  border-radius: 999px;
}

.fz-avisos-lista {
  position: absolute;
  right: 0;
  top: calc(100% + 0.4rem);
  width: 20rem;
  max-height: 20rem;
  overflow-y: auto;
  background: var(--color-superficie);
  color: var(--color-noche);
  border: 1px solid var(--color-borde);
  border-radius: 4px;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.35);
  z-index: 20;
}

.fz-avisos-item {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 0.5rem;
  padding: 0.6rem 0.75rem;
  border-bottom: 1px solid var(--color-borde);
  font-size: 0.9rem;
}

.fz-avisos-item:last-child {
  border-bottom: none;
}
</style>
