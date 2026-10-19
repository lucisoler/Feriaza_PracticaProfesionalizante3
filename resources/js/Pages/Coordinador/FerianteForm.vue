<template>
    <div class="min-h-screen bg-gray-50 p-4">
        <div class="bg-white rounded 2xl shadow-md p-6 w-full max-w-full max-w-lg mx-auto">
            <h1 class="text-xl font-bold text-green-700 mb-6">
                {{ feriante ? 'Editar feriante': 'Nuevo feriante' }}
            </h1>

            <input v-model="form.nombre" placeholder="Nombre *" class="w-full border rounded-lg p-2 mb-3"/>
            <input v-model="form.apellido" placeholder="Apellido *" class="w-full border rounded-lg p-2 mb-3"/>
            <input v-model="form.email" placeholder="Email *" class="w-full border rounded-lg p-2 mb-3"/>
            <input v-model="form.password" type="password" :placeholder="Feriante ? 'Nueva contraseña (opcional)' : 'Contraseña(mínimo 8 caracteres) *'" class="w-full border rounded-lg p-2 mb-3"/>
            <input v-model="form.telefono" placeholder="Teléfono *" class="w-full border rounded-lg p-2 mb-3"/>
            <input v-model="form.nombre_emprendimiento" placeholder="Emprendimiento" class="w-full border rounded-lg p-2 mb-3"/>


            <select v-model="form.categoria_id" class="w-full border rounded-lg p-2 mb-3">
                <option :value="null">-- Categoría --</option>
                <option v-for="c in categorias" :key="c.id">{{ c.nombre }}</option>
            </select>
            <input v-if="esOtro" v-model="form.rubro_otro" placeholder="Especificá el rubro *" class="w-full border rounded-lg p-2 mb-3"/>

            <select v-model="form.estado" class="w-full border rounded-lg p-2 mb-3">
                <option value="pendiente">Pendiente</option>
                <option value="aprovado">Aprovado</option>
                <option value="rechazado">Rechazado</option>
            </select>

            <select v-model="form.puesto_id" :disabled="!recibeMesa" class="w-full bourder rounded-lg p-2 mb-1 disabled:bg-gray-100">
                <option :value="null">Sin puesto asignado</option>
                <option v-for="p in puestosDisponibles" :key="p.id">Puesto N° {{ p.numero }}</option>
            </select>
            <p v-if="!recibeMesa" class="text-xs text-gray-500 mb-3">Esta categoría no recibe mesa : traiga la suya.</p>

                <p v-for="(error, campo) in form.errors" :key="campo" class="text-red-500 text-xs">{{ error }}</p>
<div class="flex gap-2 mt-4">
<Link href="/coordinador" class="flex-1 border border-gray-400 text-gray-600 py-2 rounded-lg textcenter">Cancelar</Link>
<button @click="guardar" :disabled="form.processing" class="flex-1 bg-green-700 text-white py-2 rounded-lg disabled:opacity-50">
{{ feriante ? 'Guardar cambios' : 'Crear feriante' }}
</button>

   </div>
      </div>
         </div>
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
const recibeMesa = computed(() => categoriaElegida.value?.recibe_mesa ?? true)

watch(recibeMesa, (valor) => {
if (!valor) form.puesto_id = null
})

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
