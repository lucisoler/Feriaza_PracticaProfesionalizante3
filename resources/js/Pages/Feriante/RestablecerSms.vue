<template>
  <main class="fz-pagina">
    <MarcaFeriaza>Ferias de la Plaza Central y el Pulmón Verde, Juan José Castelli, portal del Impenetrable</MarcaFeriaza>

    <section class="fz-tarjeta">
      <div class="fz-guarda"></div>

      <form class="fz-tarjeta-cuerpo" @submit.prevent="enviar">
        <h1 class="fz-titulo">Ingresá el código del SMS</h1>
        <p class="fz-ayuda">Escribí los 6 números que te llegaron al teléfono de tu cuenta y elegí una contraseña nueva. El código vence en 10 minutos.</p>

        <p v-if="$page.props.flash?.success" class="fz-aviso" role="status">{{ $page.props.flash.success }}</p>

        <label class="fz-campo">
          <span class="fz-etiqueta">Email de la cuenta</span>
          <input v-model="form.email" type="email" class="fz-input" :class="{ 'fz-input-error': form.errors.email }" autocomplete="email" />
          <span v-if="form.errors.email" class="fz-error">{{ form.errors.email }}</span>
        </label>

        <label class="fz-campo">
          <span class="fz-etiqueta">Código de 6 números</span>
          <input v-model="form.codigo" inputmode="numeric" autocomplete="one-time-code" maxlength="6" class="fz-input" :class="{ 'fz-input-error': form.errors.codigo }" />
          <span v-if="form.errors.codigo" class="fz-error">{{ form.errors.codigo }}</span>
        </label>

        <label class="fz-campo">
          <span class="fz-etiqueta">Contraseña nueva</span>
          <input v-model="form.password" type="password" class="fz-input" :class="{ 'fz-input-error': form.errors.password }" autocomplete="new-password" />
          <span v-if="form.errors.password" class="fz-error">{{ form.errors.password }}</span>
        </label>

        <label class="fz-campo">
          <span class="fz-etiqueta">Repetí la contraseña</span>
          <input v-model="form.password_confirmation" type="password" class="fz-input" autocomplete="new-password" />
        </label>

        <div class="fz-acciones">
          <button type="submit" class="fz-boton" :disabled="form.processing">
            {{ form.processing ? 'Guardando…' : 'Guardar contraseña' }}
          </button>
        </div>

        <p class="fz-ayuda" style="margin: 1rem 0 0">
          <Link href="/mi-cuenta/olvide-contrasena" style="color: var(--color-quebracho); font-weight: 700">Pedir otro código</Link>
        </p>
      </form>
    </section>
  </main>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import MarcaFeriaza from '../../Components/MarcaFeriaza.vue'

const props = defineProps({
  email: { type: String, default: '' },
})

const form = useForm({
  email: props.email,
  codigo: '',
  password: '',
  password_confirmation: '',
})

function enviar() {
  form.post('/mi-cuenta/restablecer-sms', {
    onFinish: () => form.reset('codigo', 'password', 'password_confirmation'),
  })
}
</script>
