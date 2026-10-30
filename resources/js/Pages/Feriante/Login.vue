<template>
  <main class="fz-pagina">
    <header class="fz-marca">
      <p class="fz-marca-nombre">Feriaza</p>
      <p class="fz-marca-lugar">Ferias de la Plaza Central y el Pulmón Verde, Juan José Castelli, portal del Impenetrable</p>
    </header>

    <section class="fz-tarjeta">
      <div class="fz-guarda"></div>

      <form class="fz-tarjeta-cuerpo" @submit.prevent="ingresar">
        <h1 class="fz-titulo">Iniciá sesión</h1>
        <p class="fz-ayuda">Entrá con tu email y contraseña para inscribirte a los eventos y elegir tu mesa.</p>

        <p v-if="$page.props.errors.social" class="fz-error" role="alert" style="margin-bottom: 1rem">{{ $page.props.errors.social }}</p>

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

        <!-- Crear cuenta con Facebook o Google -->
        <div class="fz-social">
          <p class="fz-social-titulo">¿No tenés una cuenta? Creá una con</p>
          <div class="fz-social-botones">
            <a href="/auth/facebook/redirect" class="fz-social-boton" aria-label="Crear cuenta con Facebook">
              <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true">
                <circle cx="12" cy="12" r="12" fill="#1877F2" />
                <path fill="#fff" d="M15.12 7.6h-1.6c-.63 0-.76.26-.76.9v1.42h2.36l-.31 2.4h-2.05V19h-2.5v-6.68H8.4v-2.4h1.86V8.27C10.26 6.33 11.44 5 13.63 5c.6 0 1.12.04 1.49.09z" />
              </svg>
              Facebook
            </a>
            <a href="/auth/google/redirect" class="fz-social-boton" aria-label="Crear cuenta con Google">
              <svg viewBox="0 0 48 48" width="22" height="22" aria-hidden="true">
                <path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3C33.7 32.7 29.2 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.2 7.9 3.1l5.7-5.7C34 6.1 29.3 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.4-.4-3.5z" />
                <path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.7 15.1 19 12 24 12c3.1 0 5.8 1.2 7.9 3.1l5.7-5.7C34 6.1 29.3 4 24 4 16.3 4 9.7 8.3 6.3 14.7z" />
                <path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.2 35.1 26.7 36 24 36c-5.2 0-9.6-3.3-11.3-7.9l-6.5 5C9.5 39.6 16.2 44 24 44z" />
                <path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-.8 2.2-2.2 4.2-4.1 5.6l6.2 5.2C37 39.2 44 34 44 24c0-1.3-.1-2.4-.4-3.5z" />
              </svg>
              Google
            </a>
          </div>
          <p class="fz-ayuda" style="margin: 0.9rem 0 0">
            o <Link href="/registro" style="color: var(--color-quebracho); font-weight: 700">registrate con tu email</Link>
          </p>
        </div>
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
