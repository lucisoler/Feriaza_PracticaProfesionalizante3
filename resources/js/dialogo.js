import { reactive } from 'vue'

// Estado compartido del cuadro de diálogo centrado que reemplaza a confirm() y prompt()
export const dialogo = reactive({
  abierto: false,
  titulo: '',
  mensaje: '',
  conCampo: false,
  etiquetaCampo: '',
  campo: '',
  textoConfirmar: 'Aceptar',
  peligro: false,
  resolver: null,
})

/**
 * Muestra el cuadro en el centro de la página.
 * Resuelve true al confirmar (o el texto ingresado si conCampo), y null al cancelar.
 */
export function pedirConfirmacion({
  titulo = '',
  mensaje = '',
  textoConfirmar = 'Aceptar',
  peligro = false,
  conCampo = false,
  etiquetaCampo = '',
} = {}) {
  return new Promise(resolver => {
    Object.assign(dialogo, {
      abierto: true,
      titulo,
      mensaje,
      textoConfirmar,
      peligro,
      conCampo,
      etiquetaCampo,
      campo: '',
      resolver,
    })
  })
}

export function responderDialogo(aceptado) {
  const resolver = dialogo.resolver
  const valor = aceptado ? (dialogo.conCampo ? dialogo.campo.trim() : true) : null
  dialogo.abierto = false
  dialogo.resolver = null
  resolver?.(valor || null)
}
