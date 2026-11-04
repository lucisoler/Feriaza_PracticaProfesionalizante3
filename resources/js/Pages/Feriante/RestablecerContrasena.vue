<template>
  <main class="fz-pagina">
    <header class="fz-marca">
      <p class="fz-marca-nombre">Feriaza</p>
      <p class="fz-marca-lugar">Ferias de la Plaza Central y el Pulmón Verde, Juan José Castelli, portal del Impenetrable</p>
    </header>

    <section class="fz-tarjeta">
      <div class="fz-guarda"></div>

      <form class="fz-tarjeta-cuerpo" @submit.prevent="guardar">
        <h1 class="fz-titulo">Creá una contraseña nueva</h1>

        <label class="fz-campo">
          <span class="fz-etiqueta">Email</span>
          <input v-model="form.email" type="email" class="fz-input" :class="{ 'fz-input-error': form.errors.email }" autocomplete="username" />
          <span v-if="form.errors.email" class="fz-error">{{ form.errors.email }}</span>
        </label>

        <label class="fz-campo">
          <span class="fz-etiqueta">Contraseña nueva <span class="fz-opcional">(mínimo 8 caracteres)</span></span>
          <input v-model="form.password" type="password" class="fz-input" :class="{ 'fz-input-error': form.errors.password }" autocomplete="new-password" />
          <span v-if="form.errors.password" class="fz-error">{{ form.errors.password }}</span>
        </label>

        <label class="fz-campo">
          <span class="fz-etiqueta">Repetí la contraseña nueva</span>
          <input v-model="form.password_confirmation" type="password" class="fz-input" autocomplete="new-password" />
        </label>

        <div class="fz-acciones">
          <button type="submit" class="fz-boton" :disabled="form.processing">
            {{ form.processing ? 'Guardando…' : 'Guardar contraseña' }}
          </button>
        </div>

        <p class="fz-ayuda" style="margin: 1rem 0 0">
          <Link href="/mi-cuenta/olvide-contrasena" style="color: var(--color-quebracho); font-weight: 700">Pedir otro enlace</Link>
        </p>
      </form>
    </section>
  </main>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
  token: { type: String, required: true },
  email: { type: String, default: '' },
})

const form = useForm({
  token: props.token,
  email: props.email,
  password: '',
  password_confirmation: '',
})

function guardar() {
  form.post('/mi-cuenta/restablecer', {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>
