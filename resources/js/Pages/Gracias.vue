<template>
  <main class="fz-pagina">
    <header class="fz-marca">
      <p class="fz-marca-nombre">Feriaza</p>
      <p class="fz-marca-lugar">Ferias de la Plaza Central y el Pulmón Verde, Juan José Castelli, portal del Impenetrable</p>
    </header>

    <section class="fz-tarjeta">
      <div class="fz-guarda"></div>

      <div class="fz-tarjeta-cuerpo">
        <h1 class="fz-titulo">¡Listo, ya estás inscripto!</h1>
        <p class="fz-ayuda">
          Tu inscripción y tu mesa quedaron registradas, y le avisamos al coordinador por correo.
          Acordate de reconfirmar tu puesto desde “Mi cuenta” 72 horas antes del evento.
        </p>

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
import { Link } from '@inertiajs/vue3'
</script>
