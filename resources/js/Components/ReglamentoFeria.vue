<template>
  <details class="fz-caja fz-reglamento" :open="abierto">
    <summary><strong>Plazos, pago y términos y condiciones</strong></summary>

    <h3 class="fz-reglamento-titulo">Plazos</h3>
    <ul>
      <li><strong>Inscripción:</strong> se abre dos semanas antes del evento.</li>
      <li>
        <strong>Reconfirmación del puesto:</strong> 72 horas antes del evento, desde “Mi cuenta”.
        Al reconfirmar elegís el medio de pago: efectivo o tarjeta de débito. Si elegís débito,
        podés pagar en el momento de la reconfirmación.
      </li>
      <li><strong>Pago de la mesa:</strong> en el día y la hora que confirme el coordinador.</li>
      <li><strong>Tolerancia:</strong> tenés 1 hora desde el inicio de la feria para presentarte.</li>
    </ul>

    <h3 class="fz-reglamento-titulo">Términos y condiciones</h3>
    <p>Será motivo de anulación de la cuenta:</p>
    <ol>
      <li>La no presentación (inasistencia) en el día del evento.</li>
      <li>El incumplimiento del pago requerido.</li>
    </ol>
    <p>Otras faltas que no estén mencionadas las evalúa el coordinador y también pueden ser motivo de anulación.</p>
  </details>
</template>

<script setup>
defineProps({
  abierto: { type: Boolean, default: false },
})
</script>

