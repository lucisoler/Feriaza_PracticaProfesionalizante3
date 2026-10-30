<template>
  <main class="fz-pagina">
    <div class="fz-panel">
      <PanelCabecera actual="plano" />

      <section class="fz-tarjeta">
        <div class="fz-guarda"></div>

        <div class="fz-tarjeta-cuerpo">
          <div class="fz-cabecera-tarjeta">
            <h1 class="fz-titulo">Plano de la feria</h1>
            <nav class="fz-pestanias" aria-label="Lugares">
              <Link v-for="l in lugares" :key="l.id" :href="`/coordinador/plano?lugar=${l.slug}`"
                class="fz-pestania" :class="{ 'fz-pestania-actual': l.id === lugar.id }">{{ l.nombre }}</Link>
            </nav>
          </div>

          <p v-if="$page.props.flash?.success" class="fz-aviso">{{ $page.props.flash.success }}</p>
          <p v-for="(e, clave) in erroresPlano" :key="clave" class="fz-error" role="alert">{{ e }}</p>

          <!-- Herramientas -->
          <div class="fz-herramientas" role="toolbar" aria-label="Herramientas del plano">
            <button v-for="h in herramientas" :key="h.modo" type="button" class="fz-herramienta"
              :class="{ 'fz-herramienta-actual': modo === h.modo }" :aria-pressed="modo === h.modo" @click="cambiarModo(h.modo)">
              {{ h.texto }}
            </button>
          </div>
          <p class="fz-secundario" style="margin: 0 0 0.75rem">{{ ayudaModo }}</p>

          <div class="fz-plano-grilla">
            <div>
              <LeyendaPlano :categorias="categorias" :ocupada="false" editable @cambiar-color="cambiarColor" />
              <PlanoFeria
                :lugar="lugar"
                :categorias="categorias"
                :modo="modo"
                :seleccionado="mesaActivaId"
                :seccion-activa="seccionActivaId"
                :borrador="borrador"
                @elegir-seccion="elegirSeccion"
                @elegir-mesa="elegirMesa"
                @deseleccionar="deseleccionar"
                @agregar-mesa="agregarMesa"
                @mover-mesa="moverMesa"
                @dibujar-seccion="r => { borrador = r; deseleccionar() }"
              />
            </div>

            <!-- Panel lateral -->
            <aside class="fz-plano-lateral">
              <!-- Sección nueva recién dibujada -->
              <form v-if="borrador" class="fz-caja" @submit.prevent="crearSeccion">
                <h2 class="fz-subtitulo">Nueva sección</h2>
                <label class="fz-campo">
                  <span class="fz-etiqueta">Nombre</span>
                  <input v-model="formNueva.nombre" class="fz-input" required maxlength="60" />
                </label>
                <label class="fz-campo">
                  <span class="fz-etiqueta">Categoría</span>
                  <select v-model="formNueva.categoria_id" class="fz-input">
                    <option :value="null">Sin categoría</option>
                    <option v-for="c in categorias" :key="c.id" :value="c.id">{{ c.nombre }}</option>
                  </select>
                </label>
                <label class="fz-check" style="margin-top: 0">
                  <input v-model="formNueva.no_disponible" type="checkbox" />
                  <span>Zona no disponible (no se ponen mesas)</span>
                </label>
                <div class="fz-acciones">
                  <button type="button" class="fz-boton-secundario" @click="borrador = null">Descartar</button>
                  <button type="submit" class="fz-boton" :disabled="formNueva.processing">Crear</button>
                </div>
              </form>

              <!-- Sección elegida -->
              <form v-else-if="seccionActiva" class="fz-caja" @submit.prevent="guardarSeccion">
                <h2 class="fz-subtitulo">Sección</h2>
                <label class="fz-campo">
                  <span class="fz-etiqueta">Nombre</span>
                  <input v-model="formSeccion.nombre" class="fz-input" required maxlength="60" />
                </label>
                <label class="fz-campo">
                  <span class="fz-etiqueta">Categoría</span>
                  <select v-model="formSeccion.categoria_id" class="fz-input">
                    <option :value="null">Sin categoría</option>
                    <option v-for="c in categorias" :key="c.id" :value="c.id">{{ c.nombre }}</option>
                  </select>
                </label>
                <label class="fz-check" style="margin-top: 0">
                  <input v-model="formSeccion.no_disponible" type="checkbox" />
                  <span>Zona no disponible</span>
                </label>
                <p class="fz-secundario">{{ mesasDe(seccionActiva).length }} mesas en esta sección.</p>
                <div class="fz-acciones">
                  <button type="submit" class="fz-boton" :disabled="formSeccion.processing">Guardar</button>
                </div>

                <template v-if="seccionActiva.disponible">
                  <hr class="fz-separador" />
                  <label class="fz-campo">
                    <span class="fz-etiqueta">Agregar mesas en grilla</span>
                    <div class="fz-fila">
                      <input v-model.number="cantidad" type="number" min="1" max="200" class="fz-input" style="max-width: 6rem" />
                      <button type="button" class="fz-boton-secundario" @click="completar">Distribuir</button>
                    </div>
                  </label>
                </template>

                <hr class="fz-separador" />
                <button type="button" class="fz-boton-peligro" style="width: 100%" @click="eliminarSeccion">Eliminar sección y sus mesas</button>
              </form>

              <!-- Mesa elegida -->
              <div v-else-if="mesaActiva" class="fz-caja">
                <h2 class="fz-subtitulo">Mesa N° {{ mesaActiva.numero }}</h2>
                <p class="fz-secundario">{{ seccionDe(mesaActiva)?.nombre ?? 'Sin sección' }}</p>
                <label class="fz-check">
                  <input type="checkbox" :checked="mesaActiva.habilitado" @change="habilitar(mesaActiva, $event.target.checked)" />
                  <span>Habilitada (si la sacás, se ve rayada y nadie la puede elegir)</span>
                </label>
                <div class="fz-acciones">
                  <button type="button" class="fz-boton-peligro" @click="eliminarMesa(mesaActiva)">Eliminar mesa</button>
                </div>
              </div>

              <div v-else class="fz-caja">
                <h2 class="fz-subtitulo">Cómo armar la feria</h2>
                <ol class="fz-pasos-ayuda">
                  <li>Con <strong>Dibujar sección</strong> marcá un rectángulo en el plano y asignale una categoría (toma su color).</li>
                  <li>Marcá como <strong>no disponible</strong> lo que no se usa: monumento, juegos, laguna… se ve negro con rayas grises.</li>
                  <li>Elegí una sección y usá <strong>Distribuir</strong>, o con <strong>Agregar mesa</strong> tocá donde va cada una.</li>
                  <li>Con <strong>Seleccionar y mover</strong> arrastrá las mesas para acomodarlas.</li>
                </ol>
              </div>

              <!-- Resumen -->
              <h2 class="fz-subtitulo" style="margin-top: 1.25rem">Secciones de {{ lugar.nombre }}</h2>
              <table class="fz-tabla fz-tabla-compacta">
                <thead>
                  <tr><th>Sección</th><th>Categoría</th><th>Mesas</th></tr>
                </thead>
                <tbody>
                  <tr v-for="s in lugar.secciones" :key="s.id" class="fz-fila-clickeable" @click="elegirSeccion(s)">
                    <td>{{ s.nombre }}</td>
                    <td>
                      <span v-if="!s.disponible" class="fz-chip fz-chip-bloqueado">No disponible</span>
                      <span v-else-if="categoriaDe(s)" class="fz-chip" :style="{ '--chip': categoriaDe(s).color }">{{ categoriaDe(s).nombre }}</span>
                      <span v-else class="fz-secundario">Sin categoría</span>
                    </td>
                    <td>{{ s.disponible ? mesasDe(s).length : '—' }}</td>
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
import { Link, router, useForm, usePage } from '@inertiajs/vue3'
import PanelCabecera from '../../Components/PanelCabecera.vue'
import PlanoFeria from '../../Components/PlanoFeria.vue'
import LeyendaPlano from '../../Components/LeyendaPlano.vue'

const props = defineProps({
  lugares: { type: Array, default: () => [] },
  lugar: { type: Object, required: true },
})

const page = usePage()
const categorias = computed(() => page.props.categorias ?? [])
const erroresPlano = computed(() => {
  const { seccion, puesto } = page.props.errors ?? {}
  return Object.fromEntries(Object.entries({ seccion, puesto }).filter(([, v]) => v))
})

const herramientas = [
  { modo: 'editar', texto: 'Seleccionar y mover' },
  { modo: 'agregar', texto: 'Agregar mesa' },
  { modo: 'dibujar', texto: 'Dibujar sección' },
]
const modo = ref('editar')
const ayudaModo = computed(() => ({
  editar: 'Tocá una sección o una mesa para editarla. Arrastrá las mesas para moverlas.',
  agregar: 'Tocá dentro de una sección disponible para poner una mesa nueva.',
  dibujar: 'Presioná y arrastrá sobre el plano para marcar el rectángulo de la sección.',
}[modo.value]))

function cambiarModo(m) {
  modo.value = m
  if (m !== 'dibujar') borrador.value = null
}

// Selección por id, para que siga apuntando a los datos nuevos después de cada recarga
const seccionActivaId = ref(null)
const mesaActivaId = ref(null)
const borrador = ref(null)
const cantidad = ref(10)

const seccionActiva = computed(() => props.lugar.secciones.find(s => s.id === seccionActivaId.value) ?? null)
const mesaActiva = computed(() => props.lugar.puestos.find(p => p.id === mesaActivaId.value) ?? null)

// Al cambiar de lugar se limpia la selección
watch(() => props.lugar.id, () => { deseleccionar(); borrador.value = null })

function mesasDe(s) {
  return props.lugar.puestos.filter(p => p.seccion_id === s.id)
}

function seccionDe(p) {
  return props.lugar.secciones.find(s => s.id === p.seccion_id)
}

function categoriaDe(s) {
  return categorias.value.find(c => c.id === s.categoria_id)
}

function deseleccionar() {
  seccionActivaId.value = null
  mesaActivaId.value = null
}

const opciones = { preserveScroll: true, preserveState: true }

// ---------- Secciones ----------

const formSeccion = useForm({ nombre: '', categoria_id: null, no_disponible: false })

function elegirSeccion(s) {
  borrador.value = null
  mesaActivaId.value = null
  seccionActivaId.value = s.id
  formSeccion.defaults({ nombre: s.nombre, categoria_id: s.categoria_id, no_disponible: !s.disponible })
  formSeccion.reset()
}

function guardarSeccion() {
  formSeccion
    .transform(d => ({ nombre: d.nombre, categoria_id: d.categoria_id, disponible: !d.no_disponible }))
    .patch(`/coordinador/secciones/${seccionActivaId.value}`, opciones)
}

const formNueva = useForm({ nombre: '', categoria_id: null, no_disponible: false })

function crearSeccion() {
  formNueva
    .transform(d => ({
      lugar_id: props.lugar.id,
      nombre: d.nombre,
      categoria_id: d.no_disponible ? null : d.categoria_id,
      disponible: !d.no_disponible,
      ...borrador.value,
    }))
    .post('/coordinador/secciones', {
      ...opciones,
      onSuccess: () => { borrador.value = null; formNueva.reset(); modo.value = 'editar' },
    })
}

function completar() {
  router.post(`/coordinador/secciones/${seccionActivaId.value}/completar`, { cantidad: cantidad.value }, opciones)
}

function eliminarSeccion() {
  const s = seccionActiva.value
  if (confirm(`¿Eliminar la sección «${s.nombre}» y sus ${mesasDe(s).length} mesas?`)) {
    router.delete(`/coordinador/secciones/${s.id}`, { ...opciones, onSuccess: deseleccionar })
  }
}

// ---------- Mesas ----------

function elegirMesa(p) {
  borrador.value = null
  seccionActivaId.value = null
  mesaActivaId.value = p.id
}

function agregarMesa(punto) {
  router.post('/coordinador/puestos', { lugar_id: props.lugar.id, ...punto }, opciones)
}

function moverMesa({ id, x, y }) {
  router.patch(`/coordinador/puestos/${id}`, { x, y }, opciones)
}

function habilitar(p, habilitado) {
  router.patch(`/coordinador/puestos/${p.id}`, { habilitado }, opciones)
}

function eliminarMesa(p) {
  if (confirm(`¿Eliminar la mesa N° ${p.numero}?`)) {
    router.delete(`/coordinador/puestos/${p.id}`, { ...opciones, onSuccess: deseleccionar })
  }
}

// ---------- Colores ----------

function cambiarColor(categoria, color) {
  router.patch(`/coordinador/categorias/${categoria.id}`, { color }, opciones)
}
</script>
