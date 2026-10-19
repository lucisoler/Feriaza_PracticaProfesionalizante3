<template>

    <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2x1 shadow p-8 w-full max-w-sm">
        <h1 class="text-xl font-bold text-green-700 mb-6 text-center">
        Mi-cuenta --- Feriante
        </h1>
        <input v-model="form.email" type="email" placeholder="Email" class="w-full border rounded-lg p-2 mn-3"/>
        <input v-model="form.password" type="password" placeholder="contraseña" class="w-full border rounded-lg p-2 mb-3"/>
    <p v-if="form.errors.email" class="text-red-500 text-xs mb-3">
        {{form.errors.email}}
    </p>
    <button @click="form.post('/mi-cuenta/login')" class="w-full bg-green-700 text-white py-2 rounded-lg">
        Ingresar
    </button>
    <a href="/" class="block text-center text text-xs text-gray-400 mt-4 underline">
        Volver al formulario de inscripción</a>
</div>
</div>
</template>


<script>
    import {useForm} from '@inertiajs/vue3'
    const form =useForm({email:'', password:''})
</script>
