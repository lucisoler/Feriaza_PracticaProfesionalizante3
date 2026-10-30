<template>
  <main class="fz-pagina">
    <div class="fz-panel">
      <PanelCabecera actual="feriantes">
        <Link href="/coordinador/feriantes/crear" class="fz-boton-chico">Cargar feriante</Link>
      </PanelCabecera>

      <section class="fz-tarjeta">
        <div class="fz-guarda"></div>

        <div class="fz-tarjeta-cuerpo">
          <div class="fz-cabecera-tarjeta">
            <h1 class="fz-titulo">Feriantes</h1>
            <p class="fz-secundario">
              {{ cantidad('pendiente') }} pendientes, {{ cantidad('aprobado') }} aprobados y {{ cantidad('rechazado') }} rechazados
            </p>
          </div>

          <p v-if="$page.props.flash?.success" class="fz-aviso">{{ $page.props.flash.success }}</p>

          <label class="fz-campo fz-buscador">
            <span class="fz-etiqueta">Buscar</span>
            <input v-model="busqueda" type="search" class="fz-input" placeholder="Nombre, categoría o teléfono" />
          </label>

          <table class="fz-tabla">
            <thead>
              <tr>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Mesa</th>
                <th>Teléfono</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="f in filtrados" :key="f.id">
                <td class="fz-celda-nombre" data-etiqueta="Nombre">{{ f.nombre }} {{ f.apellido }}</td>
                <td data-etiqueta="Categoría">
                  <span>
                    {{ f.categoria?.nombre }}
                    <span v-if="f.rubro_otro" class="fz-secundario">({{ f.rubro_otro }})</span>
                  </span>
                </td>
                <td data-etiqueta="Mesa">{{ f.puesto?.numero ?? '-' }}</td>
                <td data-etiqueta="Teléfono">{{ f.telefono }}</td>
                <td data-etiqueta="Estado">
                  <span class="fz-estado" :class="`fz-estado-${f.estado}`">{{ f.estado }}</span>
                </td>
                <td class="fz-celda-acciones">
                  <div class="fz-acciones-fila">
                    <button type="button" class="fz-accion fz-accion-aprobar" :disabled="f.estado === 'aprobado'" @click="cambiarEstado(f.id, 'aprobado')">Aprobar</button>
                    <button type="button" class="fz-accion fz-accion-rechazar" :disabled="f.estado === 'rechazado'" @click="cambiarEstado(f.id, 'rechazado')">Rechazar</button>
                    <Link :href="`/coordinador/feriantes/${f.id}/editar`" class="fz-accion fz-accion-editar">Editar</Link>
                    <button type="button" class="fz-accion fz-accion-eliminar" @click="eliminar(f)">Eliminar</button>
                  </div>
                </td>
              </tr>
              <tr v-if="filtrados.length === 0">
                <td colspan="6" class="fz-tabla-vacia">
                  {{ busqueda ? 'Ningún feriante coincide con la búsqueda.' : 'Todavía no hay feriantes inscriptos.' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </main>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import PanelCabecera from '../Components/PanelCabecera.vue'

const props = defineProps({
  feriantes: {
    type: Array,
    default: () => [],
  },
})

const busqueda = ref('')

// Filtra en el navegador por nombre, apellido, categoría o teléfono
const filtrados = computed(() => {
  const texto = busqueda.value.trim().toLowerCase()
  if (!texto) return props.feriantes
  return props.feriantes.filter(f =>
    [f.nombre, f.apellido, f.categoria?.nombre, f.rubro_otro, f.telefono]
      .filter(Boolean)
      .some(valor => String(valor).toLowerCase().includes(texto))
  )
})

function cantidad(estado) {
  return props.feriantes.filter(f => f.estado === estado).length
}

function cambiarEstado(id, estado) {
  router.patch(`/coordinador/feriantes/${id}/estado`, { estado }, { preserveScroll: true })
}

function eliminar(feriante) {
  if (confirm(`¿Eliminar a ${feriante.nombre} ${feriante.apellido}? Se libera su mesa.`)) {
    router.delete(`/coordinador/feriantes/${feriante.id}`, { preserveScroll: true })
  }
}
</script>
