<template>
  <main class="fz-pagina">
    <header class="fz-marca">
      <p class="fz-marca-nombre">Feriaza</p>
      <p class="fz-marca-lugar">Feria de la Plaza San Martín, Juan José Castelli, portal del Impenetrable</p>
    </header>

    <section class="fz-tarjeta">
      <div class="fz-guarda"></div>

      <div class="fz-tarjeta-cuerpo">
        <h1 class="fz-titulo">¡Listo, ya estás inscripto!</h1>
        <p class="fz-ayuda">
          Tu mesa quedó reservada. El coordinador va a revisar tu inscripción y te avisa cuando esté aprobada.
        </p>

        <template v-if="whatsapp">
          <p class="fz-ayuda">
            Para que el coordinador se entere enseguida, mandale tus datos por WhatsApp.
          </p>
          <div class="fz-acciones">
            <a :href="whatsapp" target="_blank" rel="noopener" class="fz-boton" style="text-align: center; text-decoration: none">
              Avisar por WhatsApp
            </a>
          </div>
        </template>

        <div class="fz-acciones">
          <Link href="/mi-cuenta" class="fz-boton-secundario" style="text-align: center; text-decoration: none">
            Ir a Mi cuenta
          </Link>
        </div>
      </div>
    </section>
  </main>
</template>

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

// El link llega por la sesión: redirect('/gracias')->with('whatsapp', $link)
const whatsapp = computed(() => usePage().props.flash?.whatsapp)
</script>
