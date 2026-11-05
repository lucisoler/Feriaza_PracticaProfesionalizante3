<?php

return [

    // Número del coordinador para los avisos por WhatsApp (formato internacional, sin "+")
    'whatsapp_coordinador' => env('WHATSAPP_COORDINADOR'),

    // La inscripción a un evento se abre esta cantidad de días antes del primer día (RN-15)
    'dias_apertura_inscripcion' => (int) env('FERIA_DIAS_APERTURA_INSCRIPCION', 14),

    // La reconfirmación del puesto se habilita esta cantidad de horas antes del inicio de ese día (RN-16)
    'horas_reconfirmacion' => (int) env('FERIA_HORAS_RECONFIRMACION', 72),

    // Tolerancia para presentarse desde que empieza la feria (RN-07)
    'horas_tolerancia' => (int) env('FERIA_HORAS_TOLERANCIA', 1),

    // Eventos que dura el veto por inasistencia en un evento grande
    'eventos_veto' => (int) env('FERIA_EVENTOS_VETO', 3),

];
