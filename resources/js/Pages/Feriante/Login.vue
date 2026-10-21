<template>
  <main class="fz-pagina">
    <header class="fz-marca">
      <p class="fz-marca-nombre">Feriaza</p>
      <p class="fz-marca-lugar">Feria de la Plaza San Martín, Juan José Castelli, portal del Impenetrable</p>
    </header>

    <section class="fz-tarjeta">
      <div class="fz-guarda"></div>

      <form class="fz-tarjeta-cuerpo" @submit.prevent="ingresar">
        <h1 class="fz-titulo">Mi cuenta</h1>
        <p class="fz-ayuda">Entrá con el email y la contraseña que usaste al inscribirte.</p>

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

        <p class="fz-ayuda" style="margin-top: 1.25rem; margin-bottom: 0">
          ¿Todavía no te inscribiste? <Link href="/" style="color: var(--color-quebracho); font-weight: 700">Inscribite a la feria</Link>
        </p>
      </form>
    </section>
  </main>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'

const form = useForm({
  email: '',
  password: '',
})

function ingresar() {
  form.post('/mi-cuenta/login', {
    onFinish: () => form.reset('password'),
  })
}
</script>