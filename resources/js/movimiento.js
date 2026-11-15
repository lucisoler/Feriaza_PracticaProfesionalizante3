import { ref } from 'vue'

const CLAVE = 'fz-animaciones'

function preferenciaGuardada() {
  try {
    return localStorage.getItem(CLAVE)
  } catch {
    return null
  }
}

function prefiereMovimientoReducido() {
  return window.matchMedia?.('(prefers-reduced-motion: reduce)').matches ?? false
}

function aplicar(estado) {
  document.documentElement.setAttribute('data-animaciones', estado)
}

// Sin preferencia guardada, las animaciones siguen lo que diga el sistema
const activado = ref(
  preferenciaGuardada() !== null ? preferenciaGuardada() === 'activadas' : !prefiereMovimientoReducido(),
)
aplicar(activado.value ? 'activadas' : 'pausadas')

export function useMovimiento() {
  function alternar() {
    activado.value = !activado.value
    aplicar(activado.value ? 'activadas' : 'pausadas')
    try {
      localStorage.setItem(CLAVE, activado.value ? 'activadas' : 'pausadas')
    } catch {
      // Sin localStorage: la elección dura lo que la página abierta
    }
  }

  return { activado, alternar }
}
