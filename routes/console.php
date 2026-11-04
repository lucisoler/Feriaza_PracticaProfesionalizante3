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

// A la madrugada, cuando el día de feria anterior ya terminó seguro, se
// liberan las mesas de quienes no pagaron (RN-17).
Schedule::command('feria:liberar-mesas-sin-pago')->dailyAt('03:00');
