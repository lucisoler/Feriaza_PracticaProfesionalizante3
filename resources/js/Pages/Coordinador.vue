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
            <div>
              <h1 class="fz-titulo">Feriantes</h1>
              <p class="fz-secundario">
                Planilla de feriantes registrados. Total: {{ feriantes.length }}.
              </p>
            </div>
          </div>

          <p v-if="$page.props.flash?.success" class="fz-aviso">{{ $page.props.flash.success }}</p>

          <label class="fz-campo fz-buscador">
            <span class="fz-etiqueta">Buscar</span>
            <input v-model="busqueda" type="search" class="fz-input" placeholder="Número, nombre, categoría o teléfono" />
          </label>

          <table class="fz-tabla">
            <thead>
              <tr>
                <th>Número de feriante</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Teléfono</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="f in filtrados"
                :key="f.id"
                class="fz-fila-clickeable"
                role="link"
                tabindex="0"
                @click="ver(f)"
                @keydown.enter="ver(f)"
                @keydown.space.prevent="ver(f)"
              >
                <td data-etiqueta="Número de feriante">{{ f.numero_registro ?? '—' }}</td>
                <td class="fz-celda-nombre" data-etiqueta="Nombre">{{ f.nombre }} {{ f.apellido }}</td>
                <td data-etiqueta="Categoría">
                  <span>
                    {{ f.categoria?.nombre }}
                    <span v-if="f.rubro_otro" class="fz-secundario">({{ f.rubro_otro }})</span>
                  </span>
                </td>
                <td data-etiqueta="Teléfono">{{ f.telefono }}</td>
              </tr>
              <tr v-if="filtrados.length === 0">
                <td colspan="4" class="fz-tabla-vacia">
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

// Filtra en el navegador por número, nombre, apellido, categoría o teléfono
const filtrados = computed(() => {
  const texto = busqueda.value.trim().toLowerCase()
  if (!texto) return props.feriantes
  return props.feriantes.filter(f =>
    [f.numero_registro, f.nombre, f.apellido, f.categoria?.nombre, f.rubro_otro, f.telefono]
      .filter(Boolean)
      .some(valor => String(valor).toLowerCase().includes(texto))
  )
})

// El coordinador selecciona un feriante y va a su panel con todos sus datos
function ver(feriante) {
  router.visit(`/coordinador/feriantes/${feriante.id}`)
}
</script>
