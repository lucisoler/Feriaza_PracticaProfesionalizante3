<?php

return [

    // Número del coordinador para los avisos por WhatsApp (formato internacional, sin "+")
    'whatsapp_coordinador' => env('WHATSAPP_COORDINADOR'),

    // La inscripción a un evento se abre esta cantidad de días antes del primer día (RN-15)
    'dias_apertura_inscripcion' => (int) env('FERIA_DIAS_APERTURA_INSCRIPCION', 14),

    // La reconfirmación del puesto se habilita esta cantidad de horas antes del inicio de ese día (RN-16)
    'horas_reconfirmacion' => (int) env('FERIA_HORAS_RECONFIRMACION', 72),

];
