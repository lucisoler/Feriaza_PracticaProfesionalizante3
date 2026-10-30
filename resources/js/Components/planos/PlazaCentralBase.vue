<template>
  <!--
    Plaza Central de Juan José Castelli, digitalizada en un plano de 1000 x 700.
    Es un esquema: manzana cuadrada con veredas perimetrales, senderos en cruz
    y en diagonal, y el monumento en el centro. Las secciones de LugarSeeder
    usan estas mismas coordenadas.
  -->
  <g aria-hidden="true">
    <!-- Calles que rodean la manzana -->
    <rect width="1000" height="700" fill="#b9b3a1" />
    <g stroke="#f4efe0" stroke-width="3" stroke-dasharray="22 16">
      <line x1="0" y1="25" x2="1000" y2="25" />
      <line x1="0" y1="675" x2="1000" y2="675" />
      <line x1="25" y1="0" x2="25" y2="700" />
      <line x1="975" y1="0" x2="975" y2="700" />
    </g>

    <!-- Vereda perimetral -->
    <rect x="50" y="50" width="900" height="600" rx="10" fill="#ebe3cb" stroke="#cfc5a8" stroke-width="2" />

    <!-- Césped -->
    <rect x="145" y="140" width="710" height="420" rx="6" fill="#c3d4a0" />

    <!-- Senderos: cruz y diagonales -->
    <g stroke="#ebe3cb" stroke-linecap="round">
      <line x1="145" y1="140" x2="855" y2="560" stroke-width="34" />
      <line x1="855" y1="140" x2="145" y2="560" stroke-width="34" />
      <line x1="500" y1="130" x2="500" y2="570" stroke-width="40" />
      <line x1="135" y1="350" x2="865" y2="350" stroke-width="40" />
    </g>

    <!-- Rotonda y monumento central -->
    <circle cx="500" cy="350" r="96" fill="#ebe3cb" stroke="#cfc5a8" stroke-width="2" />
    <circle cx="500" cy="350" r="40" fill="#b5ad95" />
    <circle cx="500" cy="350" r="14" fill="#6e6650" />

    <!-- Árboles -->
    <g fill="#5f7a3e" fill-opacity="0.85">
      <circle v-for="(a, i) in arboles" :key="i" :cx="a[0]" :cy="a[1]" :r="a[2]" />
    </g>

    <!-- Norte -->
    <g transform="translate(960 92)" fill="#1e2316">
      <path d="M0 -22 L8 6 L0 0 L-8 6 Z" />
      <text y="22" text-anchor="middle" font-size="16" font-weight="700">N</text>
    </g>
  </g>
</template>

<script setup>
// [x, y, radio]: árboles del césped, lejos de senderos y secciones iniciales
const arboles = [
  [395, 175, 14], [605, 175, 14], [395, 525, 14], [605, 525, 14],
  [430, 290, 11], [570, 290, 11], [430, 410, 11], [570, 410, 11],
  [270, 300, 12], [730, 300, 12], [270, 400, 12], [730, 400, 12],
  [160, 155, 10], [840, 155, 10], [160, 545, 10], [840, 545, 10],
]
</script>
