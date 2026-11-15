<template>
  <main class="fz-pagina">
    <MarcaFeriaza>Ferias de la Plaza Central y el Pulmón Verde, Juan José Castelli, portal del Impenetrable</MarcaFeriaza>

    <section class="fz-tarjeta">
      <div class="fz-guarda"></div>

      <div class="fz-tarjeta-cuerpo">
        <div class="fz-cabecera-tarjeta">
          <h1 class="fz-titulo">Hola, {{ feriante.nombre }}</h1>
          <button type="button" class="fz-enlace" @click="cerrarSesion">Cerrar sesión</button>
        </div>

        <!-- Consultas: por WhatsApp al coordinador -->
        <div class="fz-caja">
          <p class="fz-ayuda">Cualquier consulta comuniquese en el WhatsApp</p>
          <a
            v-if="whatsapp"
            :href="`https://wa.me/${whatsapp}`"
            target="_blank"
            rel="noopener"
            class="fz-boton"
            style="display: inline-flex; align-items: center; gap: 0.5rem"
          >
            <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true">
              <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
            </svg>
            WhatsApp
          </a>
        </div>

        <p v-if="$page.props.flash?.success" class="fz-aviso">{{ $page.props.flash.success }}</p>

        <p v-if="feriante.estado === 'anulado'" class="fz-error fz-caja">
          Tu cuenta fue anulada por el coordinador<template v-if="feriante.motivo_anulacion">: {{ feriante.motivo_anulacion }}</template>.
          No podés inscribirte a nuevos eventos.
        </p>

        <!-- Resumen de la inscripción -->
        <dl class="fz-resumen">
          <dt>Número de feriante</dt>
          <dd>{{ feriante.numero_registro ?? '—' }}</dd>
          <dt>Categoría</dt>
          <dd>{{ feriante.categoria?.nombre ?? 'Sin categoría' }}</dd>
          <dt>Inscripciones</dt>
          <dd>{{ inscripciones.length }}</dd>
        </dl>

        <div class="fz-cabecera-tarjeta">
          <h2 class="fz-subtitulo">Mis inscripciones</h2>
          <Link v-if="feriante.estado !== 'anulado'" href="/mi-cuenta/inscripciones/nueva" class="fz-boton-chico">Inscribirme a un evento</Link>
        </div>

        <p v-if="$page.props.errors.inscripcion" class="fz-error">{{ $page.props.errors.inscripcion }}</p>

        <ul v-if="inscripciones.length" class="fz-lista">
          <li v-for="i in inscripciones" :key="i.id" class="fz-lista-item">
            <div>
              <strong>{{ i.evento }}</strong>
              <span class="fz-secundario"> · {{ i.lugar }}</span>
              <p class="fz-secundario" style="margin: 0.15rem 0 0">
                {{ formatoDia(i.fecha) }}, desde las {{ i.hora }} ·
                {{ i.mesa ? `Mesa N° ${i.mesa}` : (feriante.categoria?.recibe_mesa === false ? 'Traés tu mesa' : 'Sin mesa') }}
              </p>

              <!-- Reconfirmación 72 horas antes del día que le toca -->
              <p v-if="i.confirmacion === 'confirmada'" class="fz-secundario" style="margin: 0.15rem 0 0">
                Puesto reconfirmado.
              </p>
              <form v-else-if="i.puede_reconfirmar" class="fz-fila" style="margin-top: 0.4rem; flex-wrap: wrap" @submit.prevent="reconfirmar(i)">
                <button type="submit" class="fz-boton-chico">Reconfirmar puesto</button>
              </form>
              <p v-else-if="i.vencida" class="fz-error" style="margin: 0.15rem 0 0">No reconfirmaste el puesto.</p>
              <p v-else class="fz-secundario" style="margin: 0.15rem 0 0">
                Reconfirmá tu puesto desde el {{ formatoDia(i.reconfirmacion_desde.slice(0, 10)).toLowerCase() }} a las {{ i.reconfirmacion_desde.slice(11) }}.
              </p>
            </div>
            <div class="fz-acciones-fila">
              <span class="fz-estado" :class="`fz-estado-${i.estado}`">{{ i.estado }}</span>
              <button
                v-if="i.es_hoy"
                type="button" class="fz-accion fz-accion-eliminar" @click="cederLugar(i)"
              >Desocupar mesa</button>
              <button
                v-else-if="i.evento_estado === 'abierto' || i.evento_estado === 'borrador'"
                type="button" class="fz-accion fz-accion-eliminar" @click="cancelar(i)"
              >Cancelar</button>
            </div>
          </li>
        </ul>
        <p v-else class="fz-ayuda">Todavía no te inscribiste a ningún evento.</p>

        <ReglamentoFeria />

        <hr class="fz-separador" />

        <h2 class="fz-subtitulo">Mis datos</h2>

        <form @submit.prevent="guardar">
          <label class="fz-campo">
            <span class="fz-etiqueta">Nombre</span>
            <input v-model="form.nombre" class="fz-input" :class="{ 'fz-input-error': form.errors.nombre }" autocomplete="given-name" />
            <span v-if="form.errors.nombre" class="fz-error">{{ form.errors.nombre }}</span>
          </label>

          <label class="fz-campo">
            <span class="fz-etiqueta">Apellido</span>
            <input v-model="form.apellido" class="fz-input" :class="{ 'fz-input-error': form.errors.apellido }" autocomplete="family-name" />
            <span v-if="form.errors.apellido" class="fz-error">{{ form.errors.apellido }}</span>
          </label>

          <label class="fz-campo">
            <span class="fz-etiqueta">Teléfono</span>
            <input v-model="form.telefono" type="tel" class="fz-input" :class="{ 'fz-input-error': form.errors.telefono }" autocomplete="tel" />
            <span v-if="form.errors.telefono" class="fz-error">{{ form.errors.telefono }}</span>
          </label>

          <label class="fz-campo">
            <span class="fz-etiqueta">Nombre del emprendimiento <span class="fz-opcional">(opcional)</span></span>
            <input v-model="form.nombre_emprendimiento" class="fz-input" />
          </label>

          <label class="fz-campo">
            <span class="fz-etiqueta">Categoría</span>
            <select v-model="form.categoria_id" class="fz-input" :class="{ 'fz-input-error': form.errors.categoria_id }">
              <option :value="null">Elegí una categoría</option>
              <option v-for="c in categorias" :key="c.id" :value="c.id">{{ c.nombre }}</option>
            </select>
            <span v-if="form.errors.categoria_id" class="fz-error">{{ form.errors.categoria_id }}</span>
          </label>

          <label v-if="esOtro" class="fz-campo">
            <span class="fz-etiqueta">¿Qué vendés?</span>
            <input v-model="form.rubro_otro" class="fz-input" :class="{ 'fz-input-error': form.errors.rubro_otro }" />
            <span v-if="form.errors.rubro_otro" class="fz-error">{{ form.errors.rubro_otro }}</span>
          </label>

          <label class="fz-campo">
            <span class="fz-etiqueta">Instagram <span class="fz-opcional">(opcional)</span></span>
            <input v-model="form.instagram" class="fz-input" />
          </label>

          <label class="fz-campo">
            <span class="fz-etiqueta">Facebook <span class="fz-opcional">(opcional)</span></span>
            <input v-model="form.facebook" class="fz-input" />
          </label>

          <label class="fz-campo">
            <span class="fz-etiqueta">TikTok <span class="fz-opcional">(opcional)</span></span>
            <input v-model="form.tiktok" class="fz-input" />
          </label>

          <hr class="fz-separador" />
          <p class="fz-ayuda">Completá estos dos campos solo si querés cambiar tu contraseña.</p>

          <label class="fz-campo">
            <span class="fz-etiqueta">Nueva contraseña <span class="fz-opcional">(mínimo 8 caracteres)</span></span>
            <input v-model="form.password" type="password" class="fz-input" :class="{ 'fz-input-error': form.errors.password }" autocomplete="new-password" />
            <span v-if="form.errors.password" class="fz-error">{{ form.errors.password }}</span>
          </label>

          <label class="fz-campo">
            <span class="fz-etiqueta">Repetí la nueva contraseña</span>
            <input v-model="form.password_confirmation" type="password" class="fz-input" autocomplete="new-password" />
          </label>

          <div class="fz-acciones">
            <button type="submit" class="fz-boton" :disabled="form.processing">
              {{ form.processing ? 'Guardando…' : 'Guardar cambios' }}
            </button>
          </div>
        </form>

        <hr class="fz-separador" />
        <p class="fz-ayuda">Si no vas a participar más, podés darte de baja. Se liberan tus mesas y se borran tus datos.</p>
        <div class="fz-acciones">
          <button type="button" class="fz-boton-peligro" @click="cancelarInscripcion">Cancelar mi inscripción</button>
        </div>
      </div>
    </section>
  </main>
</template>

<script setup>
import { computed } from 'vue'
import { Link, useForm, usePage, router } from '@inertiajs/vue3'
import ReglamentoFeria from '../../Components/ReglamentoFeria.vue'
import MarcaFeriaza from '../../Components/MarcaFeriaza.vue'
import { pedirConfirmacion } from '../../dialogo.js'

const props = defineProps({
  feriante: {
    type: Object,
    required: true,
  },
  // [{ id, fecha, es_hoy, estado, evento, evento_estado, lugar, hora, mesa, confirmacion, puede_reconfirmar,
  //    reconfirmacion_desde, vencida, asistencia }]
  inscripciones: {
    type: Array,
    default: () => [],
  },
  // Número del coordinador para WhatsApp (sin "+"); null si no está configurado
  whatsapp: {
    type: String,
    default: null,
  },
})

const formatoFecha = new Intl.DateTimeFormat('es-AR', { weekday: 'long', day: 'numeric', month: 'long', timeZone: 'UTC' })

function formatoDia(dia) {
  const texto = formatoFecha.format(new Date(`${dia}T00:00:00Z`))
  return texto.charAt(0).toUpperCase() + texto.slice(1)
}

async function cancelar(inscripcion) {
  const confirmado = await pedirConfirmacion({
    titulo: 'Cancelar inscripción',
    mensaje: `¿Cancelar tu inscripción a ${inscripcion.evento} del ${formatoDia(inscripcion.fecha).toLowerCase()}? Se libera tu mesa.`,
    textoConfirmar: 'Cancelar inscripción',
    peligro: true,
  })
  if (confirmado) {
    router.delete(`/mi-cuenta/inscripciones/${inscripcion.id}`, { preserveScroll: true })
  }
}

async function cederLugar(inscripcion) {
  const confirmado = await pedirConfirmacion({
    titulo: 'Desocupar mesa',
    mensaje: `¿Desocupar tu mesa en ${inscripcion.evento} de hoy? Se libera al instante y avisamos al coordinador.`,
    textoConfirmar: 'Desocupar',
    peligro: true,
  })
  if (confirmado) {
    router.patch(`/mi-cuenta/inscripciones/${inscripcion.id}/ceder-lugar`, {}, { preserveScroll: true })
  }
}

function reconfirmar(inscripcion) {
  router.patch(`/mi-cuenta/inscripciones/${inscripcion.id}/reconfirmar`, {}, { preserveScroll: true })
}

const form = useForm({
  nombre: props.feriante.nombre,
  apellido: props.feriante.apellido,
  telefono: props.feriante.telefono,
  nombre_emprendimiento: props.feriante.nombre_emprendimiento ?? '',
  categoria_id: props.feriante.categoria_id,
  rubro_otro: props.feriante.rubro_otro ?? '',
  instagram: props.feriante.instagram ?? '',
  facebook: props.feriante.facebook ?? '',
  tiktok: props.feriante.tiktok ?? '',
  password: '',
  password_confirmation: '',
})

const categorias = computed(() => usePage().props.categorias ?? [])
const esOtro = computed(() =>
  categorias.value.find(c => c.id === form.categoria_id)?.nombre === 'Otro'
)

function guardar() {
  form.patch('/mi-cuenta', {
    // Después de guardar, se vacían los campos de contraseña
    onSuccess: () => form.reset('password', 'password_confirmation'),
  })
}

function cerrarSesion() {
  router.post('/mi-cuenta/logout')
}

async function cancelarInscripcion() {
  const confirmado = await pedirConfirmacion({
    titulo: 'Darte de baja',
    mensaje: 'Se borran tus datos y se liberan tus mesas. Esta acción no se puede deshacer.',
    textoConfirmar: 'Darme de baja',
    peligro: true,
  })
  if (confirmado) {
    router.delete('/mi-cuenta')
  }
}
</script>
