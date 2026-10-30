<template>
  <!--
    Pulmón Verde de Juan José Castelli, digitalizado en un plano de 1000 x 700.
    Es un esquema: parque con senderos perimetrales, la laguna en el centro y
    el playón deportivo al este. Las secciones de LugarSeeder usan estas
    mismas coordenadas.
  -->
  <g aria-hidden="true">
    <!-- Calles -->
    <rect width="1000" height="700" fill="#b9b3a1" />
    <g stroke="#f4efe0" stroke-width="3" stroke-dasharray="22 16">
      <line x1="0" y1="25" x2="1000" y2="25" />
      <line x1="0" y1="675" x2="1000" y2="675" />
    </g>

    <!-- Terreno del parque -->
    <path
      d="M70 80 Q70 50 110 50 L900 50 Q950 50 950 100 L950 600 Q950 650 900 650 L110 650 Q60 650 60 600 Z"
      fill="#bfd39a" stroke="#9fb57a" stroke-width="3"
    />

    <!-- Senderos -->
    <g fill="#ebe3cb">
      <rect x="95" y="88" width="815" height="108" rx="40" />
      <rect x="95" y="488" width="815" height="104" rx="40" />
      <rect x="100" y="88" width="185" height="504" rx="40" />
    </g>
    <path d="M285 340 C340 230 420 215 540 222 C650 228 700 250 725 330 C700 420 650 450 540 452 C420 455 340 440 285 340"
      fill="none" stroke="#ebe3cb" stroke-width="22" />

    <!-- Laguna -->
    <ellipse cx="535" cy="335" rx="128" ry="76" fill="#8fbcd4" stroke="#6a9bb5" stroke-width="3" />
    <path d="M470 325 q12 -8 24 0 q12 8 24 0 M540 350 q12 -8 24 0 q12 8 24 0" stroke="#e8f3f8" stroke-width="2" fill="none" />

    <!-- Playón deportivo -->
    <rect x="742" y="262" width="136" height="126" fill="#d6cbae" stroke="#fff" stroke-width="3" />
    <line x1="810" y1="262" x2="810" y2="388" stroke="#fff" stroke-width="2" />
    <circle cx="810" cy="325" r="16" fill="none" stroke="#fff" stroke-width="2" />

    <!-- Árboles -->
    <g fill="#4f6b33" fill-opacity="0.85">
      <circle v-for="(a, i) in arboles" :key="i" :cx="a[0]" :cy="a[1]" :r="a[2]" />
    </g>

    <!-- Norte -->
    <g transform="translate(965 96)" fill="#1e2316">
      <path d="M0 -22 L8 6 L0 0 L-8 6 Z" />
      <text y="22" text-anchor="middle" font-size="16" font-weight="700">N</text>
    </g>
  </g>
</template>

<script setup>
// [x, y, radio]: árboles del parque, lejos de senderos y secciones iniciales
const arboles = [
  [330, 230, 14], [360, 445, 14], [700, 215, 13], [705, 455, 13],
  [320, 300, 10], [320, 390, 10], [905, 240, 12], [905, 420, 12],
  [930, 330, 10], [440, 450, 9], [630, 460, 9], [450, 220, 9], [620, 215, 9],
  [520, 470, 8], [80, 330, 9], [520, 205, 8],
]
</script>
