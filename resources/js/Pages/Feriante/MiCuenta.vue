<template>
  <div class="min-h-screen bg-gray-50 p-4">
    <div class="bg-white rounded-2xl shadow-md p-6 w-full max-w-lg mx-auto">

      <div class="flex justify-between items-center mb-6">
        <h1 class="text-xl font-bold text-green-700">Mi cuenta</h1>
        <button @click="cerrarSesion" class="text-sm text-gray-500 underline">Cerrar sesión</button>
      </div>

      
      <p v-if="$page.props.flash?.success" class="bg-green-50 text-green-700 text-sm rounded-lg p-2 mb-4">
        {{ $page.props.flash.success }}
      </p>

      
      <div class="text-sm mb-4 space-y-1">
        <p>Categoría: <strong>{{ feriante.categoria?.nombre ?? 'sin categoría' }}</strong></p>
        <p>Puesto asignado: <strong>{{ feriante.puesto?.numero ?? 'sin asignar' }}</strong></p>
        <p>
          Estado:
          <span :class="{
            'text-yellow-600': feriante.estado === 'pendiente',
            'text-green-700': feriante.estado === 'aprobado',
            'text-red-500': feriante.estado === 'rechazado',
          }">{{ feriante.estado }}</span>
        </p>
      </div>

      <h2 class="font-bold mb-3">Mis datos</h2>

      <input v-model="form.nombre" placeholder="Nombre *" class="w-full border rounded-lg p-2 mb-3" />
      <input v-model="form.apellido" placeholder="Apellido *" class="w-full border rounded-lg p-2 mb-3" />
      <input v-model="form.telefono" placeholder="Teléfono *" class="w-full border rounded-lg p-2 mb-3" />
      <input v-model="form.nombre_emprendimiento" placeholder="Nombre del emprendimiento (opcional)" class="w-full border rounded-lg p-2 mb-3" />

      
      <select v-model="form.categoria_id" class="w-full border rounded-lg p-2 mb-3">
        <option :value="null">-- Elegí tu categoría --</option>
        <option v-for="c in categorias" :key="c.id" :value="c.id">{{ c.nombre }}</option>
      </select>
      <input v-if="esOtro" v-model="form.rubro_otro" placeholder="Especificá tu rubro *" class="w-full border rounded-lg p-2 mb-3" />

      <input v-model="form.instagram" placeholder="Instagram (opcional)" class="w-full border rounded-lg p-2 mb-2" />
      <input v-model="form.facebook" placeholder="Facebook (opcional)" class="w-full border rounded-lg p-2 mb-2" />
      <input v-model="form.tiktok" placeholder="TikTok (opcional)" class="w-full border rounded-lg p-2 mb-3" />
      <textarea v-model="form.consulta" placeholder="Consulta (opcional)" class="w-full border rounded-lg p-2 mb-3 h-20"></textarea>

      <hr class="my-4" />
      <p class="text-xs text-gray-500 mb-2">Dejá estos campos vacíos si no querés cambiar tu contraseña.</p>
      <input v-model="form.password" type="password" placeholder="Nueva contraseña" class="w-full border rounded-lg p-2 mb-3" />
      <input v-model="form.password_confirmation" type="password" placeholder="Repetir nueva contraseña" class="w-full border rounded-lg p-2 mb-3" />

      
      <p v-for="(error, campo) in form.errors" :key="campo" class="text-red-500 text-xs mb-1">{{ error }}</p>

      <button @click="guardar" :disabled="form.processing"
        class="w-full bg-green-700 text-white py-2 rounded-lg mt-2 mb-3 disabled:opacity-50">
        {{ form.processing ? 'Guardando...' : 'Guardar cambios' }}
      </button>

      <button @click="cancelarInscripcion" class="w-full border border-red-500 text-red-500 py-2 rounded-lg">
        Cancelar mi inscripción
      </button>
    </div>
  </div>
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
    preserveScroll: true,
    
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
