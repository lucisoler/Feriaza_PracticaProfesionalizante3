<template>
  <div class="fz-plano">
    <svg
      ref="svg"
      :viewBox="`0 0 ${lugar.ancho} ${lugar.alto}`"
      class="fz-plano-svg"
      :class="`fz-plano-${modo}`"
      role="group"
      :aria-label="`Plano de ${lugar.nombre}`"
      @pointerdown="alPresionarFondo"
      @pointermove="alMover"
      @pointerup="alSoltar"
      @pointerleave="cancelarArrastre"
      @click="alTocarFondo"
    >
      <defs>
        <!-- Zonas no disponibles: negro con rayas grises -->
        <pattern :id="idRayas" width="12" height="12" patternUnits="userSpaceOnUse" patternTransform="rotate(45)">
          <rect width="12" height="12" fill="#151515" />
          <rect width="4" height="12" fill="#7d7d7d" />
        </pattern>
      </defs>

      <!-- Dibujo de base del lugar (calles, veredas, senderos, árboles) -->
      <component :is="base" />

      <!-- Secciones -->
      <g v-for="s in lugar.secciones" :key="`s${s.id}`" class="fz-plano-seccion" @click.stop="alTocarSeccion(s, $event)">
        <rect
          :x="s.x" :y="s.y" :width="s.ancho" :height="s.alto" rx="4"
          :fill="s.disponible ? colorSeccion(s) : `url(#${idRayas})`"
          :fill-opacity="s.disponible ? 0.2 : 0.94"
          :stroke="s.id === seccionActiva ? '#d9a21b' : (s.disponible ? colorSeccion(s) : '#151515')"
          :stroke-width="s.id === seccionActiva ? 4 : 2"
          :stroke-dasharray="s.id === seccionActiva ? '8 5' : null"
        >
          <title>{{ s.nombre }}{{ s.disponible ? ` · ${nombreCategoria(s)}` : ' · no disponible' }}</title>
        </rect>
        <text
          v-if="!s.disponible"
          :x="s.x + s.ancho / 2" :y="s.y + s.alto / 2"
          class="fz-plano-etiqueta-bloqueada" text-anchor="middle" dominant-baseline="middle"
        >{{ s.nombre }}</text>
        <text
          v-else-if="mesasDe(s).length === 0"
          :x="s.x + s.ancho / 2" :y="s.y + s.alto / 2"
          class="fz-plano-etiqueta" text-anchor="middle" dominant-baseline="middle"
        >{{ s.nombre }}</text>
      </g>

      <!-- Mesas -->
      <g
        v-for="p in mesasVisibles"
        :key="`p${p.id}`"
        class="fz-plano-mesa"
        :class="{ 'fz-plano-mesa-activa': esClickeable(p) }"
        :transform="`translate(${pos(p).x} ${pos(p).y})`"
        :opacity="atenuada(p) ? 0.3 : 1"
        :role="esClickeable(p) ? 'button' : null"
        :tabindex="esClickeable(p) ? 0 : null"
        :aria-label="etiquetaMesa(p)"
        :aria-pressed="modo === 'elegir' ? seleccionado === p.id : null"
        @pointerdown.stop="alPresionarMesa(p, $event)"
        @click.stop="alTocarMesa(p)"
        @keydown.enter.prevent="alTocarMesa(p, true)"
        @keydown.space.prevent="alTocarMesa(p, true)"
      >
        <rect
          :x="-MESA_W / 2" :y="-MESA_H / 2" :width="MESA_W" :height="MESA_H" rx="3"
          :fill="estilo(p).fill" :stroke="estilo(p).stroke" :stroke-width="estilo(p).ancho"
        />
        <text y="0.5" text-anchor="middle" dominant-baseline="middle" :fill="estilo(p).texto" class="fz-plano-numero">{{ p.numero }}</text>
        <title>{{ etiquetaMesa(p) }}</title>
      </g>

      <!-- Sección que se está dibujando o que espera nombre -->
      <rect
        v-if="rectDibujo"
        :x="rectDibujo.x" :y="rectDibujo.y" :width="rectDibujo.ancho" :height="rectDibujo.alto"
        fill="#d9a21b" fill-opacity="0.18" stroke="#d9a21b" stroke-width="3" stroke-dasharray="8 5"
        pointer-events="none"
      />
    </svg>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import PlazaCentralBase from './planos/PlazaCentralBase.vue'
import PulmonVerdeBase from './planos/PulmonVerdeBase.vue'

const MESA_W = 26
const MESA_H = 18
const BASES = { 'plaza-central': PlazaCentralBase, 'pulmon-verde': PulmonVerdeBase }

const props = defineProps({
  // { nombre, slug, ancho, alto, secciones: [...], puestos: [...] }
  lugar: { type: Object, required: true },
  categorias: { type: Array, default: () => [] },
  // ver | elegir (feriante) | editar | agregar | dibujar (coordinador)
  modo: { type: String, default: 'ver' },
  // Ids de las mesas ocupadas en el día que se está viendo
  ocupados: { type: Array, default: () => [] },
  // En modo "elegir", solo se pueden tocar las mesas de las secciones de esta categoría
  categoriaPermitida: { type: Number, default: null },
  seleccionado: { type: Number, default: null },
  seccionActiva: { type: Number, default: null },
  // Sección recién dibujada que todavía no se guardó
  borrador: { type: Object, default: null },
  // Texto extra por mesa para el título emergente (ej.: nombre del feriante)
  detalles: { type: Object, default: () => ({}) },
})

const emit = defineEmits(['elegir-mesa', 'elegir-seccion', 'agregar-mesa', 'mover-mesa', 'dibujar-seccion', 'deseleccionar'])

// Cada plano en la página necesita su propio id de patrón
const idRayas = `fz-rayas-${Math.random().toString(36).slice(2, 8)}`
const svg = ref(null)

const base = computed(() => BASES[props.lugar.slug] ?? null)
const ocupadosSet = computed(() => new Set(props.ocupados))
const seccionesPorId = computed(() => Object.fromEntries(props.lugar.secciones.map(s => [s.id, s])))
const categoriasPorId = computed(() => Object.fromEntries(props.categorias.map(c => [c.id, c])))

// Mesas sin posición (no deberían quedar) no se dibujan
const mesasVisibles = computed(() => props.lugar.puestos.filter(p => p.x !== null && p.y !== null))

function mesasDe(seccion) {
  return mesasVisibles.value.filter(p => p.seccion_id === seccion.id)
}

function colorSeccion(s) {
  return categoriasPorId.value[s.categoria_id]?.color ?? '#8a8a7a'
}

function nombreCategoria(s) {
  return categoriasPorId.value[s.categoria_id]?.nombre ?? 'sin categoría'
}

// ---------- Estado de cada mesa ----------

function bloqueada(p) {
  const s = seccionesPorId.value[p.seccion_id]
  return !p.habilitado || !s || !s.disponible
}

function deOtraCategoria(p) {
  return seccionesPorId.value[p.seccion_id]?.categoria_id !== props.categoriaPermitida
}

function elegible(p) {
  return !bloqueada(p) && !ocupadosSet.value.has(p.id) && !deOtraCategoria(p)
}

function atenuada(p) {
  return props.modo === 'elegir' && !bloqueada(p) && deOtraCategoria(p)
}

function esClickeable(p) {
  if (props.modo === 'elegir') return elegible(p)
  return props.modo === 'editar' || props.modo === 'ver'
}

// Texto oscuro sobre colores claros y blanco sobre los oscuros
function colorTexto(hex) {
  const n = parseInt(hex.slice(1), 16)
  const luz = (0.299 * (n >> 16) + 0.587 * ((n >> 8) & 255) + 0.114 * (n & 255)) / 255
  return luz > 0.6 ? '#1e2316' : '#ffffff'
}

function estilo(p) {
  if (props.seleccionado === p.id) {
    return { fill: '#d9a21b', stroke: '#1e2316', ancho: 2.5, texto: '#1e2316' }
  }
  if (bloqueada(p)) {
    return { fill: `url(#${idRayas})`, stroke: '#151515', ancho: 1.5, texto: '#ffffff' }
  }
  if (ocupadosSet.value.has(p.id)) {
    return { fill: '#a8a49a', stroke: '#6f6b62', ancho: 1.5, texto: '#33302a' }
  }
  const color = colorSeccion(seccionesPorId.value[p.seccion_id])
  return { fill: color, stroke: '#1e2316', ancho: 1, texto: colorTexto(color) }
}

function etiquetaMesa(p) {
  const s = seccionesPorId.value[p.seccion_id]
  let estado = 'libre'
  if (bloqueada(p)) estado = 'no disponible'
  else if (ocupadosSet.value.has(p.id)) estado = 'ocupada'
  else if (props.modo === 'elegir' && deOtraCategoria(p)) estado = 'de otra categoría'
  const extra = props.detalles[p.id] ? ` · ${props.detalles[p.id]}` : ''
  return `Mesa ${p.numero}${s ? `, ${s.nombre}` : ''}, ${estado}${extra}`
}

// ---------- Interacción ----------

// Posición en unidades del plano a partir del puntero
function punto(evento) {
  const ctm = svg.value.getScreenCTM()
  const pt = new DOMPoint(evento.clientX, evento.clientY).matrixTransform(ctm.inverse())
  return {
    x: Math.round(Math.min(Math.max(pt.x, 0), props.lugar.ancho)),
    y: Math.round(Math.min(Math.max(pt.y, 0), props.lugar.alto)),
  }
}

// Mesas movidas que esperan la respuesta del servidor, para que no "salten" atrás
const movidas = ref({})
watch(() => props.lugar.puestos, () => { movidas.value = {} })

const arrastre = ref(null) // { id, inicio, actual, movio }
const dibujo = ref(null)   // { inicio, actual }

function pos(p) {
  if (arrastre.value?.id === p.id) return arrastre.value.actual
  return movidas.value[p.id] ?? { x: p.x, y: p.y }
}

const rectDibujo = computed(() => {
  if (dibujo.value) {
    const { inicio: a, actual: b } = dibujo.value
    return { x: Math.min(a.x, b.x), y: Math.min(a.y, b.y), ancho: Math.abs(a.x - b.x), alto: Math.abs(a.y - b.y) }
  }
  return props.borrador
})

function alPresionarMesa(p, evento) {
  if (props.modo !== 'editar') return
  svg.value.setPointerCapture(evento.pointerId)
  arrastre.value = { id: p.id, inicio: punto(evento), actual: { x: p.x, y: p.y }, desde: { x: p.x, y: p.y }, movio: false }
}

function alPresionarFondo(evento) {
  if (props.modo !== 'dibujar') return
  svg.value.setPointerCapture(evento.pointerId)
  const inicio = punto(evento)
  dibujo.value = { inicio, actual: inicio }
}

function alMover(evento) {
  if (arrastre.value) {
    const actual = punto(evento)
    const a = arrastre.value
    const dx = actual.x - a.inicio.x
    const dy = actual.y - a.inicio.y
    if (Math.abs(dx) + Math.abs(dy) > 3) a.movio = true
    a.actual = { x: a.desde.x + dx, y: a.desde.y + dy }
  } else if (dibujo.value) {
    dibujo.value.actual = punto(evento)
  }
}

function alSoltar() {
  if (arrastre.value) {
    const a = arrastre.value
    arrastre.value = null
    if (a.movio) {
      movidas.value = { ...movidas.value, [a.id]: a.actual }
      ignorarClick = true
      emit('mover-mesa', { id: a.id, ...a.actual })
    }
  } else if (dibujo.value) {
    const r = rectDibujo.value
    dibujo.value = null
    ignorarClick = true
    if (r.ancho >= 20 && r.alto >= 20) emit('dibujar-seccion', r)
  }
}

function cancelarArrastre() {
  arrastre.value = null
  dibujo.value = null
}

// Después de arrastrar, el navegador igual dispara un click: se descarta
let ignorarClick = false

function alTocarMesa(p, teclado = false) {
  if (ignorarClick && !teclado) {
    ignorarClick = false
    return
  }
  if (props.modo === 'agregar') return
  if (esClickeable(p)) emit('elegir-mesa', p)
}

function alTocarFondo(evento) {
  if (ignorarClick) {
    ignorarClick = false
    return
  }
  // Fuera de una sección: el servidor responde que las mesas van dentro de una
  if (props.modo === 'agregar') emit('agregar-mesa', punto(evento))
  else if (props.modo === 'editar') emit('deseleccionar')
}

function alTocarSeccion(s, evento) {
  if (ignorarClick) {
    ignorarClick = false
    return
  }
  if (props.modo === 'agregar') {
    emit('agregar-mesa', punto(evento))
  } else if (props.modo === 'editar') {
    emit('elegir-seccion', s)
  }
}
</script>
