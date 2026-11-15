<template>
  <!--
    Plaza Central de Juan José Castelli, sobre el plano de 1000 x 700.
    La base es la imagen del plano real (public/planos/plaza-central.jpg),
    centrada sin deformarse. Las secciones de LugarSeeder se dibujan encima.
  -->
  <g aria-hidden="true">
    <rect width="1000" height="700" fill="#f4efe0" />
    <image :href="'/planos/plaza-central.jpg'" x="0" y="0" width="1000" height="700" preserveAspectRatio="xMidYMid meet" />

    <!-- Norte -->
    <g transform="translate(960 92)" fill="#1e2316">
      <path d="M0 -22 L8 6 L0 0 L-8 6 Z" />
      <text y="22" text-anchor="middle" font-size="16" font-weight="700">N</text>
    </g>
  </g>
</template>
