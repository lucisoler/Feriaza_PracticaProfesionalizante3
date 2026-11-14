<template>
  <main class="fz-pagina">
    <div class="fz-panel">
      <PanelCabecera actual="asistencia" />

      <section class="fz-tarjeta">
        <div class="fz-guarda"></div>

        <div class="fz-tarjeta-cuerpo">
          <div class="fz-cabecera-tarjeta">
            <h1 class="fz-titulo">Control de asistencia</h1>
            <p v-if="evento" class="fz-secundario">
              {{ evento.nombre }} · {{ evento.lugar }} · desde las {{ evento.hora_inicio }} h
            </p>
          </div>

          <p v-if="$page.props.flash?.success" class="fz-aviso">{{ $page.props.flash.success }}</p>
          <p v-if="$page.props.errors.inscripcion" class="fz-error">{{ $page.props.errors.inscripcion }}</p>

          <p v-if="!evento" class="fz-ayuda">Todavía no hay eventos abiertos ni cerrados para controlar.</p>

          <template v-else>
            <label class="fz-campo">
              <span class="fz-etiqueta">Evento</span>
              <select class="fz-input" :value="evento.id" @change="cambiarEvento($event.target.value)">
                <option v-for="e in eventos" :key="e.id" :value="e.id">{{ e.nombre }} ({{ e.lugar }})</option>
              </select>
            </label>

            <nav v-if="dias.length > 1" class="fz-pestanias" aria-label="Días del evento" style="margin: 1rem 0">
              <button v-for="d in dias" :key="d" type="button" class="fz-pestania" :class="{ 'fz-pestania-actual': d === dia }"
                @click="cambiarDia(d)">{{ formatoDia(d) }}</button>
            </nav>

            <table class="fz-tabla">
              <thead>
                <tr><th>N° de llegada</th><th>Mesa</th><th>Feriante</th><th>Teléfono</th><th>Asistencia</th><th>Acción</th></tr>
              </thead>
              <tbody>
                <tr v-for="i in inscripciones" :key="i.id">
                  <td data-etiqueta="N° de llegada">{{ i.numero ?? '—' }}</td>
                  <td data-etiqueta="Mesa">{{ i.mesa ?? '—' }}</td>
                  <td data-etiqueta="Feriante" class="fz-celda-nombre">
                    {{ i.feriante.nombre }} {{ i.feriante.apellido }}
                    <span v-if="i.feriante.nombre_emprendimiento" class="fz-secundario">· {{ i.feriante.nombre_emprendimiento }}</span>
                  </td>
                  <td data-etiqueta="Teléfono">{{ i.feriante.telefono }}</td>
                  <td data-etiqueta="Asistencia">
                    <span class="fz-estado" :class="`fz-estado-${i.asistencia}`">{{ asistencias[i.asistencia] ?? i.asistencia }}</span>
                  </td>
                  <td data-etiqueta="Acción">
                    <button v-if="i.puede_marcar_presente" type="button" class="fz-accion fz-accion-aprobar" @click="marcarPresente(i)">
                      Marcar presente
                    </button>
                    <span v-else-if="i.asistencia === 'pendiente'" class="fz-secundario">Tolerancia hasta las {{ i.hora_fin_tolerancia }} h</span>
                  </td>
                </tr>
                <tr v-if="inscripciones.length === 0">
                  <td colspan="6" class="fz-tabla-vacia">Nadie se inscribió para este día todavía.</td>
                </tr>
              </tbody>
            </table>
          </template>
        </div>
      </section>
    </div>
  </main>
</template>

<script setup>
import { onMounted, onUnmounted, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import PanelCabecera from '../../Components/PanelCabecera.vue'

const props = defineProps({
  eventos: { type: Array, default: () => [] },
  evento: { type: Object, default: null },
  dias: { type: Array, default: () => [] },
  dia: { type: String, default: null },
  // [{ id, numero, mesa, asistencia, puede_marcar_presente, hora_fin_tolerancia, feriante: {...} }]
  inscripciones: { type: Array, default: () => [] },
})

const asistencias = { pendiente: 'Pendiente', presente: 'Presente', ausente: 'Ausente' }

const formatoFecha = new Intl.DateTimeFormat('es-AR', { weekday: 'long', day: 'numeric', month: 'long', timeZone: 'UTC' })

function formatoDia(d) {
  const texto = formatoFecha.format(new Date(`${d}T00:00:00Z`))
  return texto.charAt(0).toUpperCase() + texto.slice(1)
}

function irA(evento, dia) {
  router.get('/coordinador/asistencia', { evento, dia }, { preserveScroll: true, preserveState: true })
}

function cambiarEvento(id) {
  irA(id, null)
}

function cambiarDia(d) {
  irA(props.evento.id, d)
}

function marcarPresente(i) {
  router.patch(`/coordinador/inscripciones/${i.id}/presente`, {}, { preserveScroll: true })
}

// Se actualiza sola: los feriantes pueden reconfirmar o ceder su lugar mientras el coordinador controla
let intervalo = null
onMounted(() => {
  intervalo = setInterval(() => {
    router.reload({ only: ['inscripciones'], preserveScroll: true, preserveState: true })
  }, 20000)
})
onUnmounted(() => clearInterval(intervalo))
</script>
