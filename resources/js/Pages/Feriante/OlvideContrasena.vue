<template>
  <main class="fz-pagina">
    <MarcaFeriaza>Ferias de la Plaza Central y el Pulmón Verde, Juan José Castelli, portal del Impenetrable</MarcaFeriaza>

    <section class="fz-tarjeta">
      <div class="fz-guarda"></div>

      <form class="fz-tarjeta-cuerpo" @submit.prevent="enviar">
        <h1 class="fz-titulo">¿Olvidaste tu contraseña?</h1>
        <p class="fz-ayuda">Escribí el email de tu cuenta y elegí cómo querés recuperar la contraseña.</p>

        <p v-if="$page.props.flash?.success" class="fz-aviso" role="status">{{ $page.props.flash.success }}</p>

        <label class="fz-campo">
          <span class="fz-etiqueta">Email</span>
          <input v-model="form.email" type="email" class="fz-input" :class="{ 'fz-input-error': form.errors.email }" autocomplete="email" />
          <span v-if="form.errors.email" class="fz-error">{{ form.errors.email }}</span>
        </label>

        <fieldset class="fz-opciones-recuperacion">
          <legend class="fz-etiqueta">¿Cómo la recuperás?</legend>

          <label class="fz-opcion">
            <input v-model="form.canal" type="radio" value="email" name="canal" />
            <span>
              <strong>Por Gmail o email</strong>
              <span class="fz-ayuda" style="display: block; margin: 0">Te mandamos un enlace a tu correo. Si te registraste con Google, llega a tu Gmail.</span>
            </span>
          </label>

          <label class="fz-opcion">
            <input v-model="form.canal" type="radio" value="sms" name="canal" />
            <span>
              <strong>Por SMS</strong>
              <span class="fz-ayuda" style="display: block; margin: 0">Te mandamos un código de 6 números al teléfono cargado en tu cuenta.</span>
            </span>
          </label>
        </fieldset>

        <div class="fz-acciones">
          <button type="submit" class="fz-boton" :disabled="form.processing">
            {{ form.processing ? 'Enviando…' : form.canal === 'sms' ? 'Mandarme el código' : 'Mandarme el enlace' }}
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
import MarcaFeriaza from '../../Components/MarcaFeriaza.vue'

const form = useForm({
  email: '',
  canal: 'email',
})

function enviar() {
  form.post(form.canal === 'sms' ? '/mi-cuenta/olvide-contrasena/sms' : '/mi-cuenta/olvide-contrasena')
}
</script>

<style scoped>
.fz-opciones-recuperacion {
  border: 0;
  padding: 0;
  margin: 0 0 1.25rem;
  display: grid;
  gap: 0.6rem;
}

.fz-opcion {
  display: flex;
  gap: 0.65rem;
  align-items: flex-start;
  padding: 0.75rem;
  border: 1px solid var(--color-borde);
  border-radius: 6px;
  background: var(--color-superficie);
  cursor: pointer;
}

.fz-opcion input {
  margin-top: 0.3rem;
}
</style>
