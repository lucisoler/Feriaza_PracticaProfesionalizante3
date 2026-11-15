<template>
  <!-- Solo en modo oscuro: luciérnagas y ojos de búho sobre el monte de fondo.
       Usa el mismo encuadre (cover, alineado abajo) que public/fondo-monte.svg, en coordenadas de 1200x1600. -->
  <div class="fz-nocturno" aria-hidden="true">
    <svg viewBox="0 0 1200 1600" preserveAspectRatio="xMidYMax slice">
      <defs>
        <radialGradient id="fz-brillo-luciernaga">
          <stop offset="0" stop-color="#f4ff9e" stop-opacity="1"/>
          <stop offset="0.35" stop-color="#e6f36a" stop-opacity="0.8"/>
          <stop offset="1" stop-color="#e6f36a" stop-opacity="0"/>
        </radialGradient>
        <radialGradient id="fz-halo-ojo">
          <stop offset="0" stop-color="#ffc94a" stop-opacity="0.45"/>
          <stop offset="1" stop-color="#ffc94a" stop-opacity="0"/>
        </radialGradient>
      </defs>

      <g
        v-for="(luciernaga, indice) in luciernagas"
        :key="indice"
        class="fz-luciernaga"
        :style="{
          '--dx': luciernaga.dx + 'px',
          '--dy': luciernaga.dy + 'px',
          '--vuelo': luciernaga.vuelo + 's',
          '--brillo': luciernaga.brillo + 's',
          animationDelay: luciernaga.retraso + 's',
        }"
      >
        <g :transform="`translate(${luciernaga.x} ${luciernaga.y})`">
          <circle class="fz-luciernaga-brillo" r="9" fill="url(#fz-brillo-luciernaga)" :style="{ animationDelay: luciernaga.retraso + 's' }"/>
          <circle r="1.8" fill="#fbffd6"/>
        </g>
      </g>

      <g v-for="(par, indice) in ojosDeBuho" :key="'ojos-' + indice">
        <g class="fz-ojos-buho" :style="{ animationDelay: par.retraso + 's' }">
          <circle :cx="par.izquierda" :cy="par.y" r="16" fill="url(#fz-halo-ojo)"/>
          <circle :cx="par.derecha" :cy="par.y" r="16" fill="url(#fz-halo-ojo)"/>
          <ellipse :cx="par.izquierda" :cy="par.y" rx="6.5" ry="6" fill="#f2b233"/>
          <ellipse :cx="par.derecha" :cy="par.y" rx="6.5" ry="6" fill="#f2b233"/>
          <circle :cx="par.izquierda" :cy="par.y" r="2.8" fill="#1a1208"/>
          <circle :cx="par.derecha" :cy="par.y" r="2.8" fill="#1a1208"/>
        </g>
      </g>
    </svg>
  </div>
</template>

<script setup>
// Posiciones fijas dentro de las copas de los quebrachos de public/fondo-monte.svg
const luciernagas = [
  { x: 120, y: 640, dx: 40, dy: -30, vuelo: 9, brillo: 2.4, retraso: -1 },
  { x: 260, y: 920, dx: -30, dy: 25, vuelo: 11, brillo: 3.1, retraso: -4 },
  { x: 180, y: 1180, dx: 25, dy: -40, vuelo: 8, brillo: 2.7, retraso: -2.5 },
  { x: 420, y: 520, dx: -35, dy: 20, vuelo: 12, brillo: 3.4, retraso: -6 },
  { x: 480, y: 980, dx: 30, dy: 35, vuelo: 10, brillo: 2.2, retraso: -3 },
  { x: 610, y: 720, dx: -25, dy: -30, vuelo: 9.5, brillo: 2.9, retraso: -7 },
  { x: 700, y: 1060, dx: 40, dy: -20, vuelo: 11.5, brillo: 2.5, retraso: -0.5 },
  { x: 860, y: 600, dx: -40, dy: 30, vuelo: 8.5, brillo: 3.2, retraso: -5 },
  { x: 950, y: 900, dx: 30, dy: -25, vuelo: 10.5, brillo: 2.6, retraso: -8 },
  { x: 1080, y: 760, dx: -20, dy: 40, vuelo: 12.5, brillo: 2.8, retraso: -2 },
  { x: 1020, y: 1110, dx: -30, dy: -35, vuelo: 9, brillo: 3.5, retraso: -6.5 },
  { x: 330, y: 1400, dx: 35, dy: -20, vuelo: 10, brillo: 2.3, retraso: -4.5 },
  { x: 800, y: 1330, dx: -25, dy: 25, vuelo: 11, brillo: 2.7, retraso: -1.5 },
  { x: 1140, y: 1300, dx: -30, dy: -25, vuelo: 8, brillo: 3, retraso: -3.5 },
]

// Dos parejas de ojos: una en el quebracho del centro y otra en el de la derecha
const ojosDeBuho = [
  { izquierda: 520, derecha: 552, y: 1222, retraso: -1.2 },
  { izquierda: 1004, derecha: 1034, y: 1246, retraso: -3.6 },
]
</script>

<style scoped>
.fz-nocturno {
  display: none;
  position: fixed;
  left: 50%;
  bottom: 0;
  width: max(100vw, 75vh);
  height: max(100vh, 133.34vw);
  transform: translateX(-50%);
  pointer-events: none;
  z-index: -1;
}

:root[data-tema='oscuro'] .fz-nocturno {
  display: block;
}

.fz-nocturno svg {
  width: 100%;
  height: 100%;
  display: block;
}

.fz-luciernaga {
  animation: fz-vuelo var(--vuelo) ease-in-out infinite alternate;
}

.fz-luciernaga-brillo {
  opacity: 0.6;
  animation: fz-brillo var(--brillo) ease-in-out infinite;
}

.fz-ojos-buho {
  transform-box: fill-box;
  transform-origin: center;
  animation: fz-parpadeo 6s infinite;
}

@keyframes fz-vuelo {
  from { transform: translate(0, 0); }
  to { transform: translate(var(--dx), var(--dy)); }
}

@keyframes fz-brillo {
  0%, 100% { opacity: 0.05; }
  45%, 55% { opacity: 1; }
}

@keyframes fz-parpadeo {
  0%, 90%, 100% { transform: scaleY(1); }
  94% { transform: scaleY(0.08); }
}

</style>
