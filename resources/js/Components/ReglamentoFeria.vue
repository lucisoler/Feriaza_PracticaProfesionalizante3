<template>
  <details class="fz-caja fz-reglamento" :open="abierto">
    <summary><strong>Plazos y términos y condiciones</strong></summary>

    <h3 class="fz-reglamento-titulo">Plazos</h3>
    <ul>
      <li><strong>Inscripción:</strong> se abre dos semanas antes del evento.</li>
      <li><strong>Reconfirmación del puesto:</strong> desde 72 horas antes del evento y hasta que empieza la feria, confirmás tu puesto desde “Mi cuenta”.</li>
      <li>
        <strong>Tolerancia de llegada:</strong> tenés 1 hora desde el inicio de la feria para presentarte. Pasada esa hora
        sin que el coordinador te marque presente, se anula tu inscripción de ese día y se libera la mesa. Si el
        evento es grande, además quedás sin poder inscribirte durante los próximos 3 eventos.
      </li>
      <li><strong>Desocupar mesa:</strong> si tenés que retirarte durante la feria, avisá con el botón “Desocupar mesa” desde “Mi cuenta”: el coordinador recibe la notificación al instante.</li>
    </ul>

    <h3 class="fz-reglamento-titulo">Términos y condiciones</h3>
    <p>Será motivo de anulación de la cuenta:</p>
    <ol>
      <li>La no presentación (inasistencia) fuera de la hora de tolerancia.</li>
    </ol>
    <p>Otras faltas que no estén mencionadas las evalúa el coordinador y también pueden ser motivo de anulación. La reconfirmación y la anulación de la inscripción se avisan también por correo electrónico.</p>
  </details>
</template>

<script setup>
defineProps({
  abierto: { type: Boolean, default: false },
})
</script>

