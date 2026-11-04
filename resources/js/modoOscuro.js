import { ref } from 'vue'

const CLAVE = 'fz-tema'

function preferenciaGuardada() {
  try {
    return localStorage.getItem(CLAVE)
  } catch {
    return null
  }
}

function prefiereOscuroElSistema() {
  return window.matchMedia?.('(prefers-color-scheme: dark)').matches ?? false
}

function aplicar(tema) {
  document.documentElement.setAttribute('data-tema', tema)
}

const tema = ref(preferenciaGuardada() ?? (prefiereOscuroElSistema() ? 'oscuro' : 'claro'))
aplicar(tema.value)

export function useModoOscuro() {
  function alternar() {
    tema.value = tema.value === 'oscuro' ? 'claro' : 'oscuro'
    aplicar(tema.value)
    try {
      localStorage.setItem(CLAVE, tema.value)
    } catch {
      // Sin localStorage (navegación privada, etc.): el tema no persiste, pero sigue funcionando
    }
  }

  return { tema, alternar }
}
