<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-md p-6 w-full max-w-lg">

      <div class="flex justify-between mb-6 text-xs font-semibold">
        <span :class="paso >= 1 ? 'text-green-700' : 'text-gray-400'">1. Datos</span>
        <span :class="paso >= 2 ? 'text-green-700' : 'text-gray-400'">2. Emprendimiento</span>
        <span :class="paso >= 3 ? 'text-green-700' : 'text-gray-400'">3. Puesto</span>
      </div>

      <div v-if="paso===1">
        <h2 class="font-bold text-lg mb-4">Datos personales</h2>
        <input v-model="form.nombre" placeholder="Nombre *" class="w-full border rounded-lg p-2 mb-3" />
        <input v-model="form.apellido" placeholder="Apellido *" class="w-full border rounded-lg p-2 mb-3" />
        <input v-model="form.telefono" placeholder="Telefono *" class="w-full border rounded-lg p-2 mb-3" />
        <hr class="my-4" />
        <p class="text-xs text-gray-500 mb-2">Con esto vas a poder entrar despues a Mi cuenta.</p>
        <input v-model="form.email" type="email" placeholder="Email *" class="w-full border rounded-lg p-2 mb-3" />
        <input v-model="form.password" type="password" placeholder="Contrasena *" class="w-full border rounded-lg p-2 mb-3" />
        <input v-model="form.password_confirmation" type="password" placeholder="Repetir contrasena *" class="w-full border rounded-lg p-2 mb-3" />
        <p v-if="form.errors.nombre" class="text-red-500 text-xs">{{ form.errors.nombre }}</p>
        <p v-if="form.errors.email" class="text-red-500 text-xs">{{ form.errors.email }}</p>
        <p v-if="form.errors.password" class="text-red-500 text-xs">{{ form.errors.password }}</p>
        <button @click="paso=2" class="w-full bg-green-700 text-white py-2 rounded-lg mt-2">Siguiente</button>
      </div>

      <div v-if="paso===2">
        <h2 class="font-bold text-lg mb-4">Tu emprendimiento</h2>
        <input v-model="form.nombre_emprendimiento" placeholder="Nombre del emprendimiento (opcional)" class="w-full border rounded-lg p-2 mb-3" />
        <select v-model="form.rubro" class="w-full border rounded-lg p-2 mb-3">
          <option value="">-- Selecciona tu rubro --</option>
          <option value="artesano">Artesano</option>
          <option value="masas">Masas / Panaderia</option>
          <option value="manualidades">Manualidades</option>
          <option value="revendedor">Revendedor</option>
          <option value="otro">Otro</option>
        </select>
        <input v-if="form.rubro==='otro'" v-model="form.rubro_otro" placeholder="Especifica tu rubro *" class="w-full border rounded-lg p-2 mb-3" />
        <input v-model="form.instagram" placeholder="Instagram (opcional)" class="w-full border rounded-lg p-2 mb-2" />
        <input v-model="form.facebook" placeholder="Facebook (opcional)" class="w-full border rounded-lg p-2 mb-2" />
        <input v-model="form.tiktok" placeholder="TikTok (opcional)" class="w-full border rounded-lg p-2 mb-3" />
        <textarea v-model="form.consulta" placeholder="Buzon de consultas (opcional)" class="w-full border rounded-lg p-2 mb-3 h-20"></textarea>
        <div class="flex gap-2">
          <button @click="paso=1" class="flex-1 border border-green-700 text-green-700 py-2 rounded-lg">Atras</button>
          <button @click="paso=3" class="flex-1 bg-green-700 text-white py-2 rounded-lg">Siguiente</button>
        </div>
      </div>

      <div v-if="paso===3">
        <h2 class="font-bold text-lg mb-2">Elegi tu puesto</h2>
        <p class="text-xs text-gray-500 mb-3">Verde = libre | Rojo = reservado</p>
        <MapaPuestos :puestos="puestos" @seleccionar="form.puesto_id=$event" />
        <p v-if="form.puesto_id" class="text-green-700 text-sm mt-2 font-semibold">
          Puesto N {{ puestos.find(p=>p.id===form.puesto_id)?.numero }} seleccionado
        </p>
        <p v-if="form.errors.puesto_id" class="text-red-500 text-xs mt-1">{{ form.errors.puesto_id }}</p>
        <label class="flex items-start gap-2 mt-4 text-sm text-gray-700">
          <input type="checkbox" v-model="form.asistencia_confirmada" class="mt-1" />
          Confirmo mi asistencia. Entiendo que tengo 1 hora de tolerancia antes de ser sancionado.
        </label>
        <div class="flex gap-2 mt-4">
          <button @click="paso=2" class="flex-1 border border-green-700 text-green-700 py-2 rounded-lg">Atras</button>
          <button @click="enviar" :disabled="!form.puesto_id || !form.asistencia_confirmada || form.processing" class="flex-1 bg-green-700 text-white py-2 rounded-lg disabled:opacity-50">
            {{ form.processing ? 'Enviando...' : 'Enviar solicitud' }}
          </button>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import MapaPuestos from '../Components/MapaPuestos.vue'

const props = defineProps({
  puestos: {
    type: Array,
    default: () => [],
  },
})

const paso = ref(1)

const form = useForm({
  nombre: '',
  apellido: '',
  telefono: '',
  email: '',
  password: '',
  password_confirmation: '',
  nombre_emprendimiento: '',
  rubro: '',
  rubro_otro: '',
  instagram: '',
  facebook: '',
  tiktok: '',
  consulta: '',
  puesto_id: null,
  asistencia_confirmada: false,
})

function enviar() {
  form.post('/feriantes')
}
</script>