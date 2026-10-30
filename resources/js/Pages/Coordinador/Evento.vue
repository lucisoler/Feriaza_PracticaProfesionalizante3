<template>
  <main class="fz-pagina">
    <div class="fz-panel">
      <PanelCabecera actual="eventos" />

      <section class="fz-tarjeta">
        <div class="fz-guarda"></div>

        <div class="fz-tarjeta-cuerpo">
          <div class="fz-cabecera-tarjeta">
            <div>
              <h1 class="fz-titulo">{{ evento.nombre }}</h1>
              <p class="fz-secundario">
                {{ evento.lugar?.nombre }} · desde las {{ evento.hora_inicio.slice(0, 5) }} h ·
                <span class="fz-estado" :class="`fz-estado-evento-${evento.estado}`">{{ evento.estado }}</span>
              </p>
            </div>
            <Link href="/coordinador/eventos" class="fz-enlace">Volver a eventos</Link>
          </div>

          <!-- Días del evento -->
          <nav v-if="dias.length > 1" class="fz-pestanias" aria-label="Días del evento" style="margin-bottom: 1rem">
            <Link v-for="d in dias" :key="d" :href="`/coordinador/eventos/${evento.id}?dia=${d}`" preserve-scroll
              class="fz-pestania" :class="{ 'fz-pestania-actual': d === dia }">{{ formatoDia(d) }}</Link>
          </nav>

          <!-- Mesas libres por sección -->
          <div class="fz-resumen-secciones">
            <div v-for="r in resumen" :key="r.seccion.id" class="fz-resumen-seccion" :style="{ '--chip': r.color }">
              <strong>{{ r.seccion.nombre }}</strong>
              <span>{{ r.categoria }}</span>
              <span><b>{{ r.libres }}</b> libres de {{ r.total }}</span>
            </div>
          </div>

          <div class="fz-plano-grilla">
            <div>
              <LeyendaPlano :categorias="categorias" />
              <PlanoFeria
                :lugar="lugar"
                :categorias="categorias"
                modo="ver"
                :ocupados="ocupados"
                :seleccionado="mesaActiva?.id ?? null"
                :detalles="detalles"
                @elegir-mesa="p => (mesaActiva = p)"
              />
            </div>

            <aside class="fz-plano-lateral">
              <div v-if="mesaActiva" class="fz-caja">
                <h2 class="fz-subtitulo">Mesa N° {{ mesaActiva.numero }}</h2>
                <template v-if="inscripcionDe(mesaActiva)">
                  <p><strong>{{ inscripcionDe(mesaActiva).feriante.nombre }} {{ inscripcionDe(mesaActiva).feriante.apellido }}</strong></p>
                  <p class="fz-secundario">{{ inscripcionDe(mesaActiva).feriante.nombre_emprendimiento }}</p>
                  <p class="fz-secundario">Tel: {{ inscripcionDe(mesaActiva).feriante.telefono }}</p>
                  <span class="fz-estado" :class="`fz-estado-${inscripcionDe(mesaActiva).estado}`">{{ inscripcionDe(mesaActiva).estado }}</span>
                </template>
                <p v-else class="fz-secundario">Libre el {{ formatoDia(dia).toLowerCase() }}.</p>
              </div>
              <p v-else class="fz-secundario">Tocá una mesa para ver quién la reservó.</p>

              <h2 class="fz-subtitulo" style="margin-top: 1.25rem">Inscriptos el {{ formatoDia(dia).toLowerCase() }}</h2>
              <table class="fz-tabla fz-tabla-compacta">
                <thead>
                  <tr><th>Mesa</th><th>Feriante</th><th>Estado</th></tr>
                </thead>
                <tbody>
                  <tr v-for="i in inscripcionesOrdenadas" :key="i.id">
                    <td>{{ i.mesa ?? '—' }}</td>
                    <td>
                      <span class="fz-punto" :style="{ background: colorCategoria(i.feriante.categoria_id) }"></span>
                      {{ i.feriante.nombre }} {{ i.feriante.apellido }}
                    </td>
                    <td><span class="fz-estado" :class="`fz-estado-${i.estado}`">{{ i.estado }}</span></td>
                  </tr>
                  <tr v-if="inscripciones.length === 0">
                    <td colspan="3" class="fz-tabla-vacia">Nadie se inscribió para este día todavía.</td>
                  </tr>
                </tbody>
              </table>
            </aside>
          </div>
        </div>
      </section>
    </div>
  </main>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import PanelCabecera from '../../Components/PanelCabecera.vue'
import PlanoFeria from '../../Components/PlanoFeria.vue'
import LeyendaPlano from '../../Components/LeyendaPlano.vue'

const props = defineProps({
  evento: { type: Object, required: true },
  dias: { type: Array, default: () => [] },
  dia: { type: String, required: true },
  lugar: { type: Object, required: true },
  // [{ id, estado, confirmacion, puesto_id, mesa, feriante: {...} }]
  inscripciones: { type: Array, default: () => [] },
})

const categorias = computed(() => usePage().props.categorias ?? [])
const mesaActiva = ref(null)
watch(() => props.dia, () => { mesaActiva.value = null })

const ocupados = computed(() => props.inscripciones.filter(i => i.puesto_id).map(i => i.puesto_id))
const detalles = computed(() =>
  Object.fromEntries(props.inscripciones.filter(i => i.puesto_id).map(i => [i.puesto_id, `${i.feriante.nombre} ${i.feriante.apellido}`]))
)
const inscripcionesOrdenadas = computed(() =>
  [...props.inscripciones].sort((a, b) => (a.mesa ?? 9999) - (b.mesa ?? 9999))
)

function inscripcionDe(p) {
  return props.inscripciones.find(i => i.puesto_id === p.id)
}

function colorCategoria(id) {
  return categorias.value.find(c => c.id === id)?.color ?? '#8a8a7a'
}

// Mesas libres por sección con categoría, para ver de un vistazo cuánto lugar queda
const resumen = computed(() => {
  const ocupadosSet = new Set(ocupados.value)
  return props.lugar.secciones
    .filter(s => s.disponible && s.categoria_id)
    .map(s => {
      const mesas = props.lugar.puestos.filter(p => p.seccion_id === s.id && p.habilitado)
      const categoria = categorias.value.find(c => c.id === s.categoria_id)
      return {
        seccion: s,
        categoria: categoria?.nombre,
        color: categoria?.color,
        total: mesas.length,
        libres: mesas.filter(p => !ocupadosSet.has(p.id)).length,
      }
    })
    .filter(r => r.total > 0)
})

const formatoFecha = new Intl.DateTimeFormat('es-AR', { weekday: 'long', day: 'numeric', month: 'long', timeZone: 'UTC' })

function formatoDia(d) {
  const texto = formatoFecha.format(new Date(`${d}T00:00:00Z`))
  return texto.charAt(0).toUpperCase() + texto.slice(1)
}
</script>
