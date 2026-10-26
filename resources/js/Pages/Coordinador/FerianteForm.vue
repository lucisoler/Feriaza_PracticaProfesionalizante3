<template>
  <main class="fz-pagina">
    <header class="fz-marca">
      <p class="fz-marca-nombre">Feriaza</p>
      <p class="fz-marca-lugar">Panel del coordinador</p>
    </header>

    <section class="fz-tarjeta">
      <div class="fz-guarda"></div>

      <form class="fz-tarjeta-cuerpo" @submit.prevent="guardar">
        <h1 class="fz-titulo">{{ feriante ? `Editar a ${feriante.nombre} ${feriante.apellido}` : 'Cargar feriante' }}</h1>
        <p class="fz-ayuda">
          {{ feriante ? 'Modificá los datos que necesites y guardá los cambios.' : 'Cargá a mano a un feriante que se anotó en el cuaderno o por teléfono.' }}
        </p>

        <!-- Datos personales -->
        <label class="fz-campo">
          <span class="fz-etiqueta">Nombre</span>
          <input v-model="form.nombre" class="fz-input" :class="{ 'fz-input-error': form.errors.nombre }" />
          <span v-if="form.errors.nombre" class="fz-error">{{ form.errors.nombre }}</span>
        </label>

        <label class="fz-campo">
          <span class="fz-etiqueta">Apellido</span>
          <input v-model="form.apellido" class="fz-input" :class="{ 'fz-input-error': form.errors.apellido }" />
          <span v-if="form.errors.apellido" class="fz-error">{{ form.errors.apellido }}</span>
        </label>

        <label class="fz-campo">
          <span class="fz-etiqueta">Teléfono</span>
          <input v-model="form.telefono" type="tel" class="fz-input" :class="{ 'fz-input-error': form.errors.telefono }" />
          <span v-if="form.errors.telefono" class="fz-error">{{ form.errors.telefono }}</span>
        </label>

        <label class="fz-campo">
          <span class="fz-etiqueta">Email</span>
          <input v-model="form.email" type="email" class="fz-input" :class="{ 'fz-input-error': form.errors.email }" />
          <span v-if="form.errors.email" class="fz-error">{{ form.errors.email }}</span>
        </label>

        <label class="fz-campo">
          <span class="fz-etiqueta">
            {{ feriante ? 'Nueva contraseña' : 'Contraseña' }}
            <span class="fz-opcional">{{ feriante ? '(dejala vacía para no cambiarla)' : '(mínimo 8 caracteres)' }}</span>
          </span>
          <input v-model="form.password" type="password" class="fz-input" :class="{ 'fz-input-error': form.errors.password }" autocomplete="new-password" />
          <span v-if="form.errors.password" class="fz-error">{{ form.errors.password }}</span>
        </label>

        <hr class="fz-separador" />

        <!-- Emprendimiento -->
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
          <span class="fz-etiqueta">¿Qué vende?</span>
          <input v-model="form.rubro_otro" class="fz-input" :class="{ 'fz-input-error': form.errors.rubro_otro }" />
          <span v-if="form.errors.rubro_otro" class="fz-error">{{ form.errors.rubro_otro }}</span>
        </label>

        <hr class="fz-separador" />

        <!-- Gestión del coordinador -->
        <label class="fz-campo">
          <span class="fz-etiqueta">Estado de la inscripción</span>
          <select v-model="form.estado" class="fz-input" :class="{ 'fz-input-error': form.errors.estado }">
            <option value="pendiente">Pendiente</option>
            <option value="aprobado">Aprobado</option>
            <option value="rechazado">Rechazado</option>
          </select>
          <span v-if="form.errors.estado" class="fz-error">{{ form.errors.estado }}</span>
        </label>

        <label class="fz-campo">
          <span class="fz-etiqueta">Mesa</span>
          <select v-model="form.puesto_id" class="fz-input" :disabled="!recibeMesa" :class="{ 'fz-input-error': form.errors.puesto_id }">
            <option :value="null">Sin mesa asignada</option>
            <option v-for="p in puestosDisponibles" :key="p.id" :value="p.id">Mesa N° {{ p.numero }}</option>
          </select>
          <span v-if="!recibeMesa" class="fz-ayuda" style="display: block; margin: 0.3rem 0 0">
            Esta categoría no recibe mesa de la feria: el feriante trae la suya.
          </span>
          <span v-if="form.errors.puesto_id" class="fz-error">{{ form.errors.puesto_id }}</span>
        </label>

        <div class="fz-acciones">
          <Link href="/coordinador" class="fz-boton-secundario" style="text-align: center; text-decoration: none">Cancelar</Link>
          <button type="submit" class="fz-boton" :disabled="form.processing">
            {{ form.processing ? 'Guardando…' : (feriante ? 'Guardar cambios' : 'Cargar feriante') }}
          </button>
        </div>
      </form>
    </section>
  </main>
</template>

<script setup>
import { computed, watch } from 'vue'
import { useForm, usePage, Link } from '@inertiajs/vue3'

const props = defineProps({
  feriante: {
    type: Object,
    default: null,
  },
  puestos: {
    type: Array,
    default: () => [],
  },
})

const form = useForm({
  nombre: props.feriante?.nombre ?? '',
  apellido: props.feriante?.apellido ?? '',
  email: props.feriante?.email ?? '',
  password: '',
  telefono: props.feriante?.telefono ?? '',
  nombre_emprendimiento: props.feriante?.nombre_emprendimiento ?? '',
  categoria_id: props.feriante?.categoria_id ?? null,
  rubro_otro: props.feriante?.rubro_otro ?? '',
  estado: props.feriante?.estado ?? 'pendiente',
  puesto_id: props.feriante?.puesto?.id ?? null,
})

const categorias = computed(() => usePage().props.categorias ?? [])
const categoriaElegida = computed(() => categorias.value.find(c => c.id === form.categoria_id))
const esOtro = computed(() => categoriaElegida.value?.nombre === 'Otro')

// Revendedores (recibe_mesa = false) no tienen mesa de la feria (RN-03)
const recibeMesa = computed(() => categoriaElegida.value?.recibe_mesa ?? true)

watch(recibeMesa, (valor) => {
  if (!valor) form.puesto_id = null
})

// Mesas libres, más la que ya tiene asignada este feriante
const puestosDisponibles = computed(() =>
  props.puestos.filter(p => p.estado === 'libre' || p.id === props.feriante?.puesto?.id)
)

function guardar() {
  if (props.feriante) {
    form.patch(`/coordinador/feriantes/${props.feriante.id}`)
  } else {
    form.post('/coordinador/feriantes')
  }
}
</script>
