<template>
  <main class="fz-pagina">
    <div class="fz-panel">
      <PanelCabecera actual="eventos" />

      <section class="fz-tarjeta">
        <div class="fz-guarda"></div>

        <div class="fz-tarjeta-cuerpo">
          <h1 class="fz-titulo">Eventos</h1>
          <p v-if="$page.props.flash?.success" class="fz-aviso">{{ $page.props.flash.success }}</p>
          <p v-if="$page.props.errors.evento" class="fz-error" role="alert">{{ $page.props.errors.evento }}</p>

          <!-- Alta / edición -->
          <form class="fz-caja fz-form-evento" @submit.prevent="guardar">
            <h2 class="fz-subtitulo">{{ editando ? 'Editar evento' : 'Nuevo evento' }}</h2>
            <div class="fz-form-grilla">
              <label class="fz-campo fz-campo-ancho">
                <span class="fz-etiqueta">Nombre</span>
                <input v-model="form.nombre" class="fz-input" :class="{ 'fz-input-error': form.errors.nombre }" />
                <span v-if="form.errors.nombre" class="fz-error">{{ form.errors.nombre }}</span>
              </label>
              <label class="fz-campo">
                <span class="fz-etiqueta">Lugar</span>
                <select v-model="form.lugar_id" class="fz-input" :class="{ 'fz-input-error': form.errors.lugar_id }">
                  <option :value="null">Elegí el lugar</option>
                  <option v-for="l in lugares" :key="l.id" :value="l.id">{{ l.nombre }}</option>
                </select>
                <span v-if="form.errors.lugar_id" class="fz-error">{{ form.errors.lugar_id }}</span>
              </label>
              <label class="fz-campo">
                <span class="fz-etiqueta">Estado</span>
                <select v-model="form.estado" class="fz-input">
                  <option value="borrador">Borrador</option>
                  <option value="abierto">Abierto (se pueden inscribir)</option>
                  <option value="cerrado">Cerrado</option>
                  <option value="cancelado">Cancelado</option>
                </select>
              </label>
              <label class="fz-campo">
                <span class="fz-etiqueta">Primer día</span>
                <input v-model="form.fecha_inicio" type="date" class="fz-input" :class="{ 'fz-input-error': form.errors.fecha_inicio }" />
                <span v-if="form.errors.fecha_inicio" class="fz-error">{{ form.errors.fecha_inicio }}</span>
              </label>
              <label class="fz-campo">
                <span class="fz-etiqueta">Último día</span>
                <input v-model="form.fecha_fin" type="date" class="fz-input" :class="{ 'fz-input-error': form.errors.fecha_fin }" />
                <span v-if="form.errors.fecha_fin" class="fz-error">{{ form.errors.fecha_fin }}</span>
              </label>
              <label class="fz-campo">
                <span class="fz-etiqueta">Hora de inicio</span>
                <input v-model="form.hora_inicio" type="time" class="fz-input" :class="{ 'fz-input-error': form.errors.hora_inicio }" />
                <span v-if="form.errors.hora_inicio" class="fz-error">{{ form.errors.hora_inicio }}</span>
              </label>
            </div>
            <div class="fz-acciones">
              <button v-if="editando" type="button" class="fz-boton-secundario" @click="cancelarEdicion">Cancelar</button>
              <button type="submit" class="fz-boton" :disabled="form.processing">{{ editando ? 'Guardar cambios' : 'Crear evento' }}</button>
            </div>
          </form>

          <table class="fz-tabla">
            <thead>
              <tr>
                <th>Evento</th>
                <th>Lugar</th>
                <th>Días</th>
                <th>Estado</th>
                <th>Inscripciones</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="e in eventos" :key="e.id">
                <td class="fz-celda-nombre" data-etiqueta="Evento">{{ e.nombre }}</td>
                <td data-etiqueta="Lugar">{{ e.lugar?.nombre ?? '—' }}</td>
                <td data-etiqueta="Días">
                  {{ fecha(e.fecha_inicio) }}<template v-if="e.fecha_fin !== e.fecha_inicio"> al {{ fecha(e.fecha_fin) }}</template>
                  <span class="fz-secundario"> · {{ e.hora_inicio.slice(0, 5) }} h</span>
                </td>
                <td data-etiqueta="Estado"><span class="fz-estado" :class="`fz-estado-evento-${e.estado}`">{{ e.estado }}</span></td>
                <td data-etiqueta="Inscripciones">{{ e.inscripciones_count }}</td>
                <td class="fz-celda-acciones">
                  <div class="fz-acciones-fila">
                    <Link :href="`/coordinador/eventos/${e.id}`" class="fz-accion fz-accion-aprobar">Ver plano</Link>
                    <button type="button" class="fz-accion fz-accion-editar" @click="editar(e)">Editar</button>
                    <button type="button" class="fz-accion fz-accion-eliminar" @click="eliminar(e)">Eliminar</button>
                  </div>
                </td>
              </tr>
              <tr v-if="eventos.length === 0">
                <td colspan="6" class="fz-tabla-vacia">Todavía no hay eventos cargados.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </main>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import PanelCabecera from '../../Components/PanelCabecera.vue'

defineProps({
  eventos: { type: Array, default: () => [] },
  lugares: { type: Array, default: () => [] },
})

const vacio = { nombre: '', lugar_id: null, fecha_inicio: '', fecha_fin: '', hora_inicio: '17:00', estado: 'borrador' }
const form = useForm({ ...vacio })
const editando = ref(null)

function editar(e) {
  editando.value = e.id
  Object.assign(form, {
    nombre: e.nombre,
    lugar_id: e.lugar_id,
    fecha_inicio: e.fecha_inicio,
    fecha_fin: e.fecha_fin,
    hora_inicio: e.hora_inicio.slice(0, 5),
    estado: e.estado,
  })
  form.clearErrors()
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function cancelarEdicion() {
  editando.value = null
  Object.assign(form, vacio)
  form.clearErrors()
}

function guardar() {
  const opciones = { preserveScroll: true, onSuccess: cancelarEdicion }
  if (editando.value) form.patch(`/coordinador/eventos/${editando.value}`, opciones)
  else form.post('/coordinador/eventos', opciones)
}

function eliminar(e) {
  if (confirm(`¿Eliminar el evento «${e.nombre}»?`)) {
    router.delete(`/coordinador/eventos/${e.id}`, { preserveScroll: true })
  }
}

function fecha(valor) {
  const [a, m, d] = valor.split('-')
  return `${d}/${m}/${a}`
}
</script>
