<template>
  <main class="fz-pagina">
    <header class="fz-marca">
      <p class="fz-marca-nombre">Feriaza</p>
      <p class="fz-marca-lugar">Ferias de la Plaza Central y el Pulmón Verde, Juan José Castelli, portal del Impenetrable</p>
    </header>

    <section class="fz-tarjeta">
      <div class="fz-guarda"></div>

      <div class="fz-tarjeta-cuerpo">
        <div class="fz-cabecera-tarjeta">
          <h1 class="fz-titulo">Hola, {{ feriante.nombre }}</h1>
          <button type="button" class="fz-enlace" @click="cerrarSesion">Cerrar sesión</button>
        </div>

        <p v-if="$page.props.flash?.success" class="fz-aviso">{{ $page.props.flash.success }}</p>

        <!-- Resumen de la inscripción -->
        <dl class="fz-resumen">
          <dt>Estado</dt>
          <dd><span class="fz-estado" :class="`fz-estado-${feriante.estado}`">{{ feriante.estado }}</span></dd>
          <dt>Categoría</dt>
          <dd>{{ feriante.categoria?.nombre ?? 'Sin categoría' }}</dd>
          <dt>Inscripciones</dt>
          <dd>{{ inscripciones.length }}</dd>
        </dl>

        <div class="fz-cabecera-tarjeta">
          <h2 class="fz-subtitulo">Mis inscripciones</h2>
          <Link href="/mi-cuenta/inscripciones/nueva" class="fz-boton-chico">Inscribirme a un evento</Link>
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
            </div>
            <div class="fz-acciones-fila">
              <span class="fz-estado" :class="`fz-estado-${i.estado}`">{{ i.estado }}</span>
              <button
                v-if="i.evento_estado === 'abierto' || i.evento_estado === 'borrador'"
                type="button" class="fz-accion fz-accion-eliminar" @click="cancelar(i)"
              >Cancelar</button>
            </div>
          </li>
        </ul>
        <p v-else class="fz-ayuda">Todavía no te inscribiste a ningún evento.</p>

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

          <label class="fz-campo">
            <span class="fz-etiqueta">Consulta para el coordinador <span class="fz-opcional">(opcional)</span></span>
            <textarea v-model="form.consulta" class="fz-input" rows="3"></textarea>
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

const props = defineProps({
  feriante: {
    type: Object,
    required: true,
  },
  // [{ id, fecha, estado, evento, evento_estado, lugar, hora, mesa }]
  inscripciones: {
    type: Array,
    default: () => [],
  },
})

const formatoFecha = new Intl.DateTimeFormat('es-AR', { weekday: 'long', day: 'numeric', month: 'long', timeZone: 'UTC' })

function formatoDia(dia) {
  const texto = formatoFecha.format(new Date(`${dia}T00:00:00Z`))
  return texto.charAt(0).toUpperCase() + texto.slice(1)
}

function cancelar(inscripcion) {
  if (confirm(`¿Cancelar tu inscripción a ${inscripcion.evento} del ${formatoDia(inscripcion.fecha).toLowerCase()}? Se libera tu mesa.`)) {
    router.delete(`/mi-cuenta/inscripciones/${inscripcion.id}`, { preserveScroll: true })
  }
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
  consulta: props.feriante.consulta ?? '',
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

function cancelarInscripcion() {
  if (confirm('¿Seguro que querés cancelar tu inscripción? Esta acción no se puede deshacer.')) {
    router.delete('/mi-cuenta')
  }
}
</script>
