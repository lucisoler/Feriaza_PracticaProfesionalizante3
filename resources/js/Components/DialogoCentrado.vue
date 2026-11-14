<template>
  <Transition name="fz-dialogo">
    <div v-if="dialogo.abierto" class="fz-dialogo-fondo" @click.self="responderDialogo(false)" @keydown.esc="responderDialogo(false)">
      <section
        class="fz-tarjeta fz-dialogo"
        role="dialog"
        aria-modal="true"
        :aria-labelledby="'fz-dialogo-titulo'"
      >
        <div class="fz-guarda"></div>

        <form class="fz-tarjeta-cuerpo" @submit.prevent="responderDialogo(true)">
          <h2 id="fz-dialogo-titulo" class="fz-subtitulo">{{ dialogo.titulo }}</h2>
          <p v-if="dialogo.mensaje" class="fz-ayuda">{{ dialogo.mensaje }}</p>

          <label v-if="dialogo.conCampo" class="fz-campo">
            <span class="fz-etiqueta">{{ dialogo.etiquetaCampo }}</span>
            <input v-model="dialogo.campo" class="fz-input" autofocus />
          </label>

          <div class="fz-acciones fz-dialogo-acciones">
            <button type="button" class="fz-boton-chico" @click="responderDialogo(false)">Cancelar</button>
            <button type="submit" :class="dialogo.peligro ? 'fz-boton-peligro' : 'fz-boton'">
              {{ dialogo.textoConfirmar }}
            </button>
          </div>
        </form>
      </section>
    </div>
  </Transition>
</template>

<script setup>
import { dialogo, responderDialogo } from '../dialogo.js'
</script>

<style>
/* Fondo borroso que hace resaltar el cuadro centrado */
.fz-dialogo-fondo {
  position: fixed;
  inset: 0;
  z-index: 50;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  background: rgba(20, 16, 10, 0.45);
  -webkit-backdrop-filter: blur(6px);
  backdrop-filter: blur(6px);
}

.fz-dialogo {
  width: 100%;
  max-width: 28rem;
  margin: 0;
}

.fz-dialogo-acciones {
  justify-content: flex-end;
  margin-top: 1rem;
}

.fz-dialogo-enter-active,
.fz-dialogo-leave-active {
  transition: opacity 0.15s ease;
}

.fz-dialogo-enter-from,
.fz-dialogo-leave-to {
  opacity: 0;
}
</style>
