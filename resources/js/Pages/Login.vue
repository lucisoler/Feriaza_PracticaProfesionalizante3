<template>
  <main class="fz-pagina">
    <header class="fz-marca">
      <p class="fz-marca-nombre">Feriaza</p>
      <p class="fz-marca-lugar">Panel del coordinador de la feria</p>
    </header>

    <section class="fz-tarjeta">
      <div class="fz-guarda"></div>

      <form class="fz-tarjeta-cuerpo" @submit.prevent="ingresar">
        <h1 class="fz-titulo">Ingresar como coordinador</h1>
        <p class="fz-ayuda">Usá el email y la contraseña que te asignaron.</p>

        <label class="fz-campo">
          <span class="fz-etiqueta">Email</span>
          <input v-model="form.email" type="email" class="fz-input" :class="{ 'fz-input-error': form.errors.email }" autocomplete="username" />
          <span v-if="form.errors.email" class="fz-error">{{ form.errors.email }}</span>
        </label>

        <label class="fz-campo">
          <span class="fz-etiqueta">Contraseña</span>
          <input v-model="form.password" type="password" class="fz-input" :class="{ 'fz-input-error': form.errors.password }" autocomplete="current-password" />
          <span v-if="form.errors.password" class="fz-error">{{ form.errors.password }}</span>
        </label>

        <div class="fz-acciones">
          <button type="submit" class="fz-boton" :disabled="form.processing">
            {{ form.processing ? 'Ingresando…' : 'Ingresar' }}
          </button>
        </div>
      </form>
    </section>
  </main>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
  email: '',
  password: '',
})

function ingresar() {
  form.post('/login', {
    onFinish: () => form.reset('password'),
  })
}
</script>
