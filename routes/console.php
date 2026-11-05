<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// La reconfirmación se abre 72 horas antes de cada día de feria (RN-16): se
// revisa cada hora para avisar lo antes posible sin mandar correos de más.
Schedule::command('feria:recordar-reconfirmacion')->hourly();

// Vencida la hora de tolerancia, se anula la inscripción de quien no se
// presentó y, si el evento es grande, queda vetado (RN-07, RN-08).
Schedule::command('feria:aplicar-tolerancia')->everyTenMinutes();
