<template>
  <main class="fz-pagina">
    <header class="fz-marca">
      <p class="fz-marca-nombre">Feriaza</p>
      <p class="fz-marca-lugar">Ferias de la Plaza Central y el Pulmón Verde, Juan José Castelli, portal del Impenetrable</p>
    </header>

    <section class="fz-tarjeta">
      <div class="fz-guarda"></div>

      <form class="fz-tarjeta-cuerpo" @submit.prevent="enviar">
        <h1 class="fz-titulo">¿Olvidaste tu contraseña?</h1>
        <p class="fz-ayuda">Escribí el email de tu cuenta y te mandamos un enlace para crear una contraseña nueva.</p>

        <p v-if="$page.props.flash?.success" class="fz-aviso" role="status">{{ $page.props.flash.success }}</p>

        <label class="fz-campo">
          <span class="fz-etiqueta">Email</span>
          <input v-model="form.email" type="email" class="fz-input" :class="{ 'fz-input-error': form.errors.email }" autocomplete="email" />
          <span v-if="form.errors.email" class="fz-error">{{ form.errors.email }}</span>
        </label>

        <div class="fz-acciones">
          <button type="submit" class="fz-boton" :disabled="form.processing">
            {{ form.processing ? 'Enviando…' : 'Mandarme el enlace' }}
          </button>
        </div>

        <p class="fz-ayuda" style="margin: 1rem 0 0">
          <Link href="/mi-cuenta/login" style="color: var(--color-quebracho); font-weight: 700">Volver a iniciar sesión</Link>
        </p>
      </form>
    </section>
  </main>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'

const form = useForm({
  email: '',
})

function enviar() {
  form.post('/mi-cuenta/olvide-contrasena')
}
</script>
