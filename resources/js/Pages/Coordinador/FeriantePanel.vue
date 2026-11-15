<template>
  <main class="fz-pagina">
    <div class="fz-panel">
      <PanelCabecera actual="feriantes" />

      <section class="fz-tarjeta">
        <div class="fz-guarda"></div>

        <div class="fz-tarjeta-cuerpo">
          <div class="fz-cabecera-tarjeta">
            <div>
              <h1 class="fz-titulo">{{ feriante.nombre }} {{ feriante.apellido }}</h1>
              <p class="fz-secundario">Panel del feriante y todos sus datos.</p>
            </div>
            <Link :href="`/coordinador/feriantes/${feriante.id}/editar`" class="fz-boton-chico">Editar datos</Link>
          </div>

          <p v-if="$page.props.flash?.success" class="fz-aviso">{{ $page.props.flash.success }}</p>

          <p v-if="feriante.estado === 'anulado'" class="fz-error fz-caja">
            Cuenta anulada<template v-if="feriante.motivo_anulacion">: {{ feriante.motivo_anulacion }}</template>.
          </p>

          <dl class="fz-resumen">
            <dt>Número de feriante</dt>
            <dd>{{ feriante.numero_registro ?? '—' }}</dd>
            <dt>Email</dt>
            <dd>{{ feriante.email }}</dd>
            <dt>Teléfono</dt>
            <dd>{{ feriante.telefono }}</dd>
            <dt>Emprendimiento</dt>
            <dd>{{ feriante.nombre_emprendimiento || '—' }}</dd>
            <dt>Categoría</dt>
            <dd>
              {{ feriante.categoria?.nombre ?? 'Sin categoría' }}
              <span v-if="feriante.rubro_otro" class="fz-secundario">({{ feriante.rubro_otro }})</span>
            </dd>
            <dt>Instagram</dt>
            <dd>{{ feriante.instagram || '—' }}</dd>
            <dt>Facebook</dt>
            <dd>{{ feriante.facebook || '—' }}</dd>
            <dt>TikTok</dt>
            <dd>{{ feriante.tiktok || '—' }}</dd>
            <dt>Inscripciones</dt>
            <dd>{{ feriante.inscripciones_count }}</dd>
            <dt>Alta</dt>
            <dd>{{ formatoAlta }}</dd>
          </dl>

          <div class="fz-acciones">
            <Link href="/coordinador" class="fz-boton-secundario" style="text-align: center; text-decoration: none">Volver a la lista</Link>
          </div>
        </div>
      </section>
    </div>
  </main>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import PanelCabecera from '../../Components/PanelCabecera.vue'

const props = defineProps({
  feriante: {
    type: Object,
    required: true,
  },
})

const formatoFecha = new Intl.DateTimeFormat('es-AR', { day: 'numeric', month: 'long', year: 'numeric' })
const formatoAlta = computed(() =>
  props.feriante.created_at ? formatoFecha.format(new Date(props.feriante.created_at)) : '—'
)
</script>
