<template>
  <main class="fz-pagina">
    <MarcaFeriaza>Ferias de la Plaza Central y el Pulmón Verde, Juan José Castelli, portal del Impenetrable</MarcaFeriaza>

    <!-- Entrada tranquila: un búho invita a abrir el panel. Si ya hay un aviso o un error (vuelve de Google o de una acción), el panel se muestra directo. -->
    <button v-if="!panelAbierto" type="button" class="fz-buho-boton" aria-label="Mostrar el inicio de sesión" @click="abrirPanel">
      <svg class="fz-buho" viewBox="0 0 120 120" aria-hidden="true">
        <path class="fz-buho-ala" style="--signo: 1" d="M30 70 Q18 88 28 104 Q38 96 40 80 Z" />
        <path class="fz-buho-ala" style="--signo: -1" d="M90 70 Q102 88 92 104 Q82 96 80 80 Z" />
        <polygon class="fz-buho-cuerpo" points="28,38 34,10 46,30" />
        <polygon class="fz-buho-cuerpo" points="92,38 86,10 74,30" />
        <ellipse class="fz-buho-cuerpo" cx="60" cy="72" rx="36" ry="40" />
        <g class="fz-buho-ojos">
          <circle class="fz-buho-disco" cx="46" cy="56" r="16" />
          <circle class="fz-buho-disco" cx="74" cy="56" r="16" />
          <circle class="fz-buho-iris" cx="46" cy="56" r="9" />
          <circle class="fz-buho-iris" cx="74" cy="56" r="9" />
          <circle class="fz-buho-pupila" cx="46" cy="56" r="4" />
          <circle class="fz-buho-pupila" cx="74" cy="56" r="4" />
        </g>
        <polygon class="fz-buho-pico" points="56,68 64,68 60,78" />
        <path class="fz-buho-pecho" d="M46 92 Q60 100 74 92 Q60 106 46 92 Z" />
      </svg>
      <span class="fz-globo">¡Apretá aquí!</span>
    </button>

    <section v-else class="fz-tarjeta fz-tarjeta-aparece">
      <div class="fz-guarda"></div>

      <form class="fz-tarjeta-cuerpo" @submit.prevent="ingresar">
        <h1 class="fz-titulo">Iniciá sesión</h1>
        <p class="fz-ayuda">Entrá con tu email y contraseña para inscribirte a los eventos y elegir tu mesa.</p>

        <p v-if="$page.props.flash?.success" class="fz-aviso" role="status">{{ $page.props.flash.success }}</p>
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

        <p class="fz-ayuda" style="margin: -0.5rem 0 0; text-align: right">
          <Link href="/mi-cuenta/olvide-contrasena" style="color: var(--color-quebracho); font-weight: 700">¿Olvidaste tu contraseña?</Link>
        </p>

        <div class="fz-acciones">
          <button type="submit" class="fz-boton" :disabled="form.processing">
            {{ form.processing ? 'Ingresando…' : 'Ingresar' }}
          </button>
        </div>

        <!-- Crear cuenta con Google -->
        <div class="fz-social">
          <p class="fz-social-titulo">¿No tenés una cuenta? Creá una con</p>
          <div class="fz-social-botones">
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
import { ref } from 'vue'
import { Link, usePage, useForm } from '@inertiajs/vue3'
import MarcaFeriaza from '../../Components/MarcaFeriaza.vue'

const pagina = usePage()
const panelAbierto = ref(Boolean(pagina.props.flash?.success || pagina.props.errors?.social))

const form = useForm({
  email: '',
  password: '',
})

function abrirPanel() {
  panelAbierto.value = true
}

function ingresar() {
  form.post('/mi-cuenta/login', {
    onFinish: () => form.reset('password'),
  })
}
</script>

<style scoped>
.fz-buho-boton {
  margin: 3rem auto 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
  background: none;
  border: 0;
  cursor: pointer;
  color: var(--color-noche);
  font: inherit;
}

.fz-buho {
  width: 150px;
  height: 150px;
  transform-origin: center bottom;
  animation: fz-buho-salto 2.4s ease-out infinite;
}

.fz-buho-cuerpo,
.fz-buho-ala {
  fill: #141414;
}

.fz-buho-ala {
  transform-box: fill-box;
  transform-origin: top center;
  animation: fz-buho-aleteo 2.4s ease-out infinite;
}

.fz-buho-disco {
  fill: #2a2a2a;
}

.fz-buho-iris {
  fill: #ffc94a;
}

.fz-buho-pupila {
  fill: #0a0a0a;
}

.fz-buho-pico {
  fill: #d9a21b;
}

.fz-buho-pecho {
  fill: #2a2a2a;
}

.fz-buho-ojos {
  transform-box: fill-box;
  transform-origin: center;
  animation: fz-buho-parpadeo 5s infinite;
}

.fz-globo {
  position: relative;
  padding: 0.5rem 1.1rem;
  border-radius: 999px;
  background: var(--color-superficie);
  border: 2px solid var(--color-quebracho);
  color: var(--color-quebracho);
  font-family: var(--font-serif);
  font-weight: 800;
  font-size: 1.15rem;
  animation: fz-pulso 1.6s ease-in-out infinite;
}

.fz-tarjeta-aparece {
  animation: fz-reaparece 0.9s ease-in both;
}

@keyframes fz-buho-salto {
  0%, 100% { transform: translateY(0) scale(1, 1) rotate(0); }
  8% { transform: translateY(2px) scale(1.08, 0.92) rotate(0); }
  22% { transform: translateY(-48px) scale(0.96, 1.05) rotate(-6deg); }
  34% { transform: translateY(0) scale(1.1, 0.9) rotate(3deg); }
  42% { transform: translateY(0) scale(1, 1) rotate(0); }
  58% { transform: translateX(-10px) rotate(-4deg); }
  70% { transform: translateX(8px) rotate(3deg); }
  82% { transform: translateX(0) rotate(0); }
}

@keyframes fz-buho-aleteo {
  0%, 8%, 42%, 100% { transform: rotate(0); }
  22% { transform: rotate(calc(var(--signo) * 38deg)); }
  30% { transform: rotate(calc(var(--signo) * -10deg)); }
}

@keyframes fz-buho-parpadeo {
  0%, 90%, 100% { transform: scaleY(1); }
  94% { transform: scaleY(0.08); }
}

@keyframes fz-pulso {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.06); }
}

@keyframes fz-reaparece {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

</style>
