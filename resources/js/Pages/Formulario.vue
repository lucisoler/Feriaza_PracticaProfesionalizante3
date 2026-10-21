<template>
  <main class="fz-pagina">
    <header class="fz-marca">
      <p class="fz-marca-nombre">Feriaza</p>
      <p class="fz-marca-lugar">Feria de la Plaza San Martín, Juan José Castelli, portal del Impenetrable</p>
    </header>

    <section class="fz-tarjeta">
      <div class="fz-guarda"></div>

      <div class="fz-tarjeta-cuerpo">
        <!-- Indicador de pasos -->
        <ol class="fz-pasos">
          <li v-for="(nombre, i) in pasos" :key="nombre" class="fz-paso" :class="clasePaso(i + 1)">
            {{ i + 1 }}. {{ nombre }}
          </li>
        </ol>

        <!-- PASO 1: datos personales y acceso -->
        <div v-if="paso === 1">
          <h1 class="fz-titulo">Tus datos</h1>
          <p class="fz-ayuda">Los usamos para avisarte cuando aprueben tu inscripción.</p>

          <label class="fz-campo">
            <span class="fz-etiqueta">Nombre</span>
            <input v-model="form.nombre" class="fz-input" :class="{ 'fz-input-error': form.errors.nombre }" autocomplete="given-name" />
            <span v-if="form.errors.nombre" class="fz-error">{{ form.errors.nombre }}</span>
          </label>

          <label class="fz-campo">
            <span class="fz-etiqueta">Apellido</span>
            <input v-model="form.apellido" class="fz-input" :class="{ 'fz-input-error': form.errors.apellido }" autocomplete="family-name" />
            <span v-if="form.errors.apellido" class="fz-error">{{ form.errors.apellido }}</span>
          </label>

          <label class="fz-campo">
            <span class="fz-etiqueta">Teléfono</span>
            <input v-model="form.telefono" type="tel" class="fz-input" :class="{ 'fz-input-error': form.errors.telefono }" autocomplete="tel" />
            <span v-if="form.errors.telefono" class="fz-error">{{ form.errors.telefono }}</span>
          </label>

          <hr class="fz-separador" />
          <p class="fz-ayuda">Con tu email y contraseña vas a poder entrar después a "Mi cuenta".</p>

          <label class="fz-campo">
            <span class="fz-etiqueta">Email</span>
            <input v-model="form.email" type="email" class="fz-input" :class="{ 'fz-input-error': form.errors.email }" autocomplete="email" />
            <span v-if="form.errors.email" class="fz-error">{{ form.errors.email }}</span>
          </label>

          <label class="fz-campo">
            <span class="fz-etiqueta">Contraseña <span class="fz-opcional">(mínimo 8 caracteres)</span></span>
            <input v-model="form.password" type="password" class="fz-input" :class="{ 'fz-input-error': form.errors.password }" autocomplete="new-password" />
            <span v-if="form.errors.password" class="fz-error">{{ form.errors.password }}</span>
          </label>

          <label class="fz-campo">
            <span class="fz-etiqueta">Repetí la contraseña</span>
            <input v-model="form.password_confirmation" type="password" class="fz-input" autocomplete="new-password" />
          </label>

          <div class="fz-acciones">
            <button type="button" class="fz-boton" @click="paso = 2">Seguir con tu emprendimiento</button>
          </div>
        </div>

        <!-- PASO 2: emprendimiento y categoría -->
        <div v-if="paso === 2">
          <h1 class="fz-titulo">Tu emprendimiento</h1>
          <p class="fz-ayuda">La categoría define la prioridad para asignar mesa.</p>

          <label class="fz-campo">
            <span class="fz-etiqueta">Nombre del emprendimiento <span class="fz-opcional">(opcional)</span></span>
            <input v-model="form.nombre_emprendimiento" class="fz-input" />
          </label>

          <label class="fz-campo">
            <span class="fz-etiqueta">Categoría</span>
            <select v-model="form.categoria_id" class="fz-input" :class="{ 'fz-input-error': form.errors.categoria_id }">
              <option :value="null">Elegí una categoría</option>
              <option v-for="c in categorias" :key="c.id" :value="c.id">{{ c.nombre }}</option>
            </select>
            <span v-if="form.errors.categoria_id" class="fz-error">{{ form.errors.categoria_id }}</span>
          </label>

          <label v-if="esOtro" class="fz-campo">
            <span class="fz-etiqueta">¿Qué vendés?</span>
            <input v-model="form.rubro_otro" class="fz-input" :class="{ 'fz-input-error': form.errors.rubro_otro }" />
            <span v-if="form.errors.rubro_otro" class="fz-error">{{ form.errors.rubro_otro }}</span>
          </label>

          <label class="fz-campo">
            <span class="fz-etiqueta">Instagram <span class="fz-opcional">(opcional)</span></span>
            <input v-model="form.instagram" class="fz-input" />
          </label>

          <label class="fz-campo">
            <span class="fz-etiqueta">Facebook <span class="fz-opcional">(opcional)</span></span>
            <input v-model="form.facebook" class="fz-input" />
          </label>

          <label class="fz-campo">
            <span class="fz-etiqueta">TikTok <span class="fz-opcional">(opcional)</span></span>
            <input v-model="form.tiktok" class="fz-input" />
          </label>

          <label class="fz-campo">
            <span class="fz-etiqueta">Consulta para el coordinador <span class="fz-opcional">(opcional)</span></span>
            <textarea v-model="form.consulta" class="fz-input" rows="3"></textarea>
          </label>

          <div class="fz-acciones">
            <button type="button" class="fz-boton-secundario" @click="paso = 1">Volver</button>
            <button type="button" class="fz-boton" @click="paso = 3">{{ recibeMesa ? 'Elegir mesa' : 'Seguir' }}</button>
          </div>
        </div>

        <!-- PASO 3: mapa de mesas (o aviso para quienes traen su propia mesa) -->
        <div v-if="paso === 3">
          <h1 class="fz-titulo">Tu mesa</h1>

          <template v-if="recibeMesa">
            <p class="fz-ayuda">Tocá una mesa libre del plano de la plaza.</p>

            <MapaPuestos :puestos="puestos" @seleccionar="form.puesto_id = $event" />

            <p v-if="form.puesto_id" class="fz-confirmacion">
              Elegiste la mesa {{ puestos.find(p => p.id === form.puesto_id)?.numero }}.
            </p>
            <span v-if="form.errors.puesto_id" class="fz-error">{{ form.errors.puesto_id }}</span>
          </template>

          <p v-else class="fz-ayuda">
            En la categoría {{ categoriaElegida?.nombre }} no se asigna mesa de la feria: traés la tuya
            y el coordinador te indica dónde ubicarte el día del evento.
          </p>

          <label class="fz-check">
            <input type="checkbox" v-model="form.asistencia_confirmada" />
            <span>Confirmo mi asistencia. Sé que tengo 1 hora de tolerancia desde el inicio de la feria; después corresponde una sanción.</span>
          </label>

          <div class="fz-acciones">
            <button type="button" class="fz-boton-secundario" @click="paso = 2">Volver</button>
            <button type="button" class="fz-boton" :disabled="(recibeMesa && !form.puesto_id) || !form.asistencia_confirmada || form.processing" @click="enviar">
              {{ form.processing ? 'Enviando…' : 'Enviar inscripción' }}
            </button>
          </div>
        </div>
      </div>
    </section>
  </main>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import MapaPuestos from '../Components/MapaPuestos.vue'

const props = defineProps({
  puestos: {
    type: Array,
    default: () => [],
  },
})

const pasos = ['Datos', 'Emprendimiento', 'Mesa']
const paso = ref(1)

function clasePaso(numero) {
  if (numero < paso.value) return 'fz-paso-hecho'
  if (numero === paso.value) return 'fz-paso-actual'
  return ''
}

const form = useForm({
  nombre: '',
  apellido: '',
  telefono: '',
  email: '',
  password: '',
  password_confirmation: '',
  nombre_emprendimiento: '',
  categoria_id: null,
  rubro_otro: '',
  instagram: '',
  facebook: '',
  tiktok: '',
  consulta: '',
  puesto_id: null,
  asistencia_confirmada: false,
})

const categorias = computed(() => usePage().props.categorias ?? [])
const categoriaElegida = computed(() =>
  categorias.value.find(c => c.id === form.categoria_id)
)
const esOtro = computed(() => categoriaElegida.value?.nombre === 'Otro')

// Revendedores (recibe_mesa = false) no eligen mesa (RN-03). Sin categoría elegida, se asume que sí.
const recibeMesa = computed(() => categoriaElegida.value?.recibe_mesa ?? true)

// Si cambia a una categoría sin mesa, se descarta la mesa elegida antes
watch(recibeMesa, (valor) => {
  if (!valor) form.puesto_id = null
})

function enviar() {
  form.post('/feriantes', {
    // Si el servidor devuelve errores de los pasos anteriores, se vuelve a ese paso
    onError: (errores) => {
      if (errores.nombre || errores.apellido || errores.telefono || errores.email || errores.password) {
        paso.value = 1
      } else if (errores.categoria_id || errores.rubro_otro) {
        paso.value = 2
      }
    },
  })
}
</script>
