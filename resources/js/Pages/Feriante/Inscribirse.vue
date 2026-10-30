<template>
  <main class="fz-pagina">
    <header class="fz-marca fz-marca-ancha">
      <p class="fz-marca-nombre">Feriaza</p>
      <p class="fz-marca-lugar">Ferias de la Plaza Central y el Pulmón Verde, Juan José Castelli</p>
    </header>

    <section class="fz-tarjeta fz-tarjeta-ancha">
      <div class="fz-guarda"></div>

      <div class="fz-tarjeta-cuerpo">
        <div class="fz-cabecera-tarjeta">
          <h1 class="fz-titulo">Inscribirme a un evento</h1>
          <Link href="/mi-cuenta" class="fz-enlace">Volver a Mi cuenta</Link>
        </div>

        <p v-if="$page.props.flash?.success" class="fz-aviso">{{ $page.props.flash.success }}</p>

        <p v-if="eventos.length === 0" class="fz-ayuda">
          Por ahora no hay eventos con la inscripción abierta. Te avisamos cuando el coordinador abra uno.
        </p>

        <template v-else>
          <!-- 1. Evento -->
          <h2 class="fz-subtitulo">1. Elegí el evento</h2>
          <div class="fz-opciones" role="radiogroup" aria-label="Eventos abiertos">
            <button
              v-for="e in eventos" :key="e.id" type="button" role="radio"
              class="fz-opcion" :class="{ 'fz-opcion-elegida': form.evento_id === e.id }"
              :aria-checked="form.evento_id === e.id"
              @click="elegirEvento(e.id)"
            >
              <strong>{{ e.nombre }}</strong>
              <span>{{ e.lugar }} · desde las {{ e.hora_inicio }}</span>
              <span class="fz-secundario">{{ rangoDias(e) }}</span>
            </button>
          </div>
          <span v-if="form.errors.evento_id" class="fz-error">{{ form.errors.evento_id }}</span>

          <!-- 2. Día -->
          <template v-if="evento">
            <h2 class="fz-subtitulo">2. Elegí el día</h2>
            <div class="fz-opciones fz-opciones-dias" role="radiogroup" aria-label="Días del evento">
              <button
                v-for="d in evento.dias" :key="d" type="button" role="radio"
                class="fz-opcion" :class="{ 'fz-opcion-elegida': form.fecha === d }"
                :aria-checked="form.fecha === d"
                :disabled="yaInscripto(d)"
                @click="elegirDia(d)"
              >
                <strong>{{ formatoDia(d) }}</strong>
                <span class="fz-secundario">{{ yaInscripto(d) ? 'Ya estás inscripto' : `${libresDelDia(d)} mesas libres de tu categoría` }}</span>
              </button>
            </div>
            <span v-if="form.errors.fecha" class="fz-error">{{ form.errors.fecha }}</span>
          </template>

          <!-- 3. Mesa -->
          <template v-if="evento && form.fecha">
            <h2 class="fz-subtitulo">3. {{ recibeMesa ? 'Elegí tu mesa' : 'Tu lugar' }}</h2>

            <p v-if="recibeMesa" class="fz-ayuda">
              Tu categoría es
              <span class="fz-chip" :style="{ '--chip': feriante.categoria?.color }">{{ feriante.categoria?.nombre }}</span>.
              Tocá una mesa libre de las secciones de ese color en el plano de {{ lugar.nombre }}.
            </p>
            <p v-else class="fz-ayuda">
              En la categoría {{ feriante.categoria?.nombre }} no se asigna mesa de la feria: traés la tuya y te ubicás
              en la sección de tu color en el plano de {{ lugar.nombre }}.
            </p>

            <LeyendaPlano :categorias="categorias" :solo-ids="categoriasDelPlano" :resaltar="feriante.categoria_id" :elegida="recibeMesa" />

            <PlanoFeria
              :lugar="lugar"
              :categorias="categorias"
              :modo="recibeMesa ? 'elegir' : 'ver'"
              :ocupados="ocupadosDelDia"
              :categoria-permitida="feriante.categoria_id"
              :seleccionado="form.puesto_id"
              @elegir-mesa="p => recibeMesa && (form.puesto_id = p.id)"
            />

            <p v-if="recibeMesa && libresDelDia(form.fecha) === 0" class="fz-error" style="margin-top: 0.75rem">
              Ya no quedan mesas libres de tu categoría ese día. Probá con otro día u otro evento.
            </p>
            <p v-if="mesaElegida" class="fz-confirmacion">
              Elegiste la mesa {{ mesaElegida.numero }} ({{ seccionDe(mesaElegida)?.nombre }}) para el {{ formatoDia(form.fecha) }}.
            </p>
            <span v-if="form.errors.puesto_id" class="fz-error">{{ form.errors.puesto_id }}</span>

            <label class="fz-check">
              <input v-model="form.acepta_reglamento" type="checkbox" />
              <span>Acepto el reglamento y confirmo mi asistencia. Sé que tengo 1 hora de tolerancia desde el inicio de la feria; después corresponde una sanción.</span>
            </label>
            <span v-if="form.errors.acepta_reglamento" class="fz-error">{{ form.errors.acepta_reglamento }}</span>

            <div class="fz-acciones">
              <button type="button" class="fz-boton" :disabled="!puedeEnviar" @click="enviar">
                {{ form.processing ? 'Enviando…' : 'Confirmar inscripción' }}
              </button>
            </div>
          </template>
        </template>
      </div>
    </section>
  </main>
</template>

<script setup>
import { computed } from 'vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'
import PlanoFeria from '../../Components/PlanoFeria.vue'
import LeyendaPlano from '../../Components/LeyendaPlano.vue'

const props = defineProps({
  feriante: { type: Object, required: true },
  // [{ id, nombre, lugar_id, lugar, hora_inicio, dias: [...], ocupacion: { dia: [puesto_id] } }]
  eventos: { type: Array, default: () => [] },
  // Lugares de esos eventos con su plano, por id
  lugares: { type: [Object, Array], default: () => ({}) },
  // "evento_id|dia" donde ya está inscripto
  inscriptas: { type: Array, default: () => [] },
  eventoInicial: { type: Number, default: null },
})

const categorias = computed(() => usePage().props.categorias ?? [])
const recibeMesa = computed(() => props.feriante.categoria?.recibe_mesa ?? true)

const inicial = props.eventos.find(e => e.id === props.eventoInicial) ?? (props.eventos.length === 1 ? props.eventos[0] : null)

const form = useForm({
  evento_id: inicial?.id ?? null,
  fecha: inicial?.dias.length === 1 ? inicial.dias[0] : null,
  puesto_id: null,
  acepta_reglamento: false,
})

const evento = computed(() => props.eventos.find(e => e.id === form.evento_id) ?? null)
const lugar = computed(() => (evento.value ? props.lugares[evento.value.lugar_id] : null))
const ocupadosDelDia = computed(() => evento.value?.ocupacion[form.fecha] ?? [])
const mesaElegida = computed(() => lugar.value?.puestos.find(p => p.id === form.puesto_id) ?? null)

const categoriasDelPlano = computed(() =>
  [...new Set((lugar.value?.secciones ?? []).filter(s => s.disponible && s.categoria_id).map(s => s.categoria_id))]
)

const puedeEnviar = computed(() =>
  form.evento_id && form.fecha && (!recibeMesa.value || form.puesto_id) && form.acepta_reglamento && !form.processing
)

function seccionDe(p) {
  return lugar.value?.secciones.find(s => s.id === p.seccion_id)
}

function yaInscripto(dia) {
  return props.inscriptas.includes(`${form.evento_id}|${dia}`)
}

// Mesas de la categoría del feriante, habilitadas y sin reservar ese día
function libresDelDia(dia) {
  if (!lugar.value) return 0
  const ocupados = new Set(evento.value.ocupacion[dia] ?? [])
  return lugar.value.puestos.filter(p => {
    const s = seccionDe(p)
    return p.habilitado && s?.disponible && s.categoria_id === props.feriante.categoria_id && !ocupados.has(p.id)
  }).length
}

function elegirEvento(id) {
  if (form.evento_id === id) return
  form.evento_id = id
  const e = evento.value
  form.fecha = e.dias.length === 1 && !yaInscripto(e.dias[0]) ? e.dias[0] : null
  form.puesto_id = null
}

function elegirDia(dia) {
  form.fecha = dia
  form.puesto_id = null
}

const formatoFecha = new Intl.DateTimeFormat('es-AR', { weekday: 'long', day: 'numeric', month: 'long', timeZone: 'UTC' })

function formatoDia(dia) {
  const texto = formatoFecha.format(new Date(`${dia}T00:00:00Z`))
  return texto.charAt(0).toUpperCase() + texto.slice(1)
}

function rangoDias(e) {
  if (e.dias.length === 1) return formatoDia(e.dias[0])
  return `${e.dias.length} días: del ${formatoDia(e.dias[0]).toLowerCase()} al ${formatoDia(e.dias.at(-1)).toLowerCase()}`
}

function enviar() {
  form.post('/mi-cuenta/inscripciones', { preserveScroll: true })
}
</script>
