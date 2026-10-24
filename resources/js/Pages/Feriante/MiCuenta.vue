<template>
  <main class="fz-pagina">
    <header class="fz-marca">
      <p class="fz-marca-nombre">Feriaza</p>
      <p class="fz-marca-lugar">Feria de la Plaza San Martín, Juan José Castelli, portal del Impenetrable</p>
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
          <dt>Mesa</dt>
          <dd>{{ feriante.puesto?.numero ? `N° ${feriante.puesto.numero}` : (feriante.categoria?.recibe_mesa === false ? 'Traés la tuya' : 'Sin asignar') }}</dd>
        </dl>

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
        <p class="fz-ayuda">Si no vas a participar más, podés darte de baja. Se libera tu mesa y se borran tus datos.</p>
        <div class="fz-acciones">
          <button type="button" class="fz-boton-peligro" @click="cancelarInscripcion">Cancelar mi inscripción</button>
        </div>
      </div>
    </section>
  </main>
</template>

<script setup>
import { computed } from 'vue'
import { useForm, usePage, router } from '@inertiajs/vue3'

const props = defineProps({
  feriante: {
    type: Object,
    required: true,
  },
})

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
