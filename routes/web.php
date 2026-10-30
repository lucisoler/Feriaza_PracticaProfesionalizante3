<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\FerianteController;
use App\Http\Controllers\PuestoController;
use App\Http\Controllers\CoordinadorController;
use App\Http\Controllers\MiCuentaController;
use App\Http\Controllers\FerianteAuthController;
use App\Http\Controllers\CoordinadorFerianteController;
use App\Http\Controllers\CuentaSocialController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\InscripcionController;
use App\Http\Controllers\PlanoController;

//Públicas Feriantes//

// La vista del feriante empieza por el inicio de sesión
Route::get('/',[FerianteAuthController::class,'showLogin']);

// Registro con email y contraseña
Route::get('/registro',[FerianteController::class,'create']);
Route::post('/feriantes',[FerianteController::class,'store']);

// Crear cuenta o entrar con Google / Facebook
Route::get('/auth/{proveedor}/redirect',[CuentaSocialController::class,'redirect'])->whereIn('proveedor',['google','facebook']);
Route::get('/auth/{proveedor}/callback',[CuentaSocialController::class,'callback'])->whereIn('proveedor',['google','facebook']);
Route::get('/registro/social',[CuentaSocialController::class,'create']);
Route::post('/registro/social',[CuentaSocialController::class,'store']);

Route::get('/gracias',function(){
    return Inertia::render('Gracias');
});


// Login del feriante (Mi cuenta) //

Route::get('/mi-cuenta/login',[FerianteAuthController::class,'showLogin'])->name('feriante.login');

Route::post('/mi-cuenta/login',[FerianteAuthController::class,'login']);
Route::post('/mi-cuenta/logout',[FerianteAuthController::class,'logout'])->middleware('auth:feriante');


// Area privada del feriante- read/Update/Delete de si mismo//

Route::middleware('auth:feriante')->prefix('mi-cuenta')->group(function (){

Route::get('/',[MiCuentaController::class, 'show']);
Route::patch('/',[MiCuentaController::class,'update']);
Route::delete('/',[MiCuentaController::class,'destroy']);

// Inscripción a un evento: evento, día y mesa en el plano del lugar
Route::get('/inscripciones/nueva',[InscripcionController::class,'create']);
Route::post('/inscripciones',[InscripcionController::class,'store']);
Route::delete('/inscripciones/{id}',[InscripcionController::class,'destroy']);
});


// ==Login del coordinador== //

Route::get('/login',fn()=>Inertia::render('Login'))->name('login');
Route::post('/login',[CoordinadorController::class,'login']);
Route::post('/logout',[CoordinadorController::class, 'logout'])->middleware('auth');


//Panel coordinador-CRUD completo//

Route::middleware('auth')->prefix('coordinador')->group(function(){
    Route::get('/',[CoordinadorController::class,'index']);


    Route::get('/feriantes/crear',[CoordinadorFerianteController::class,'create']);
    Route::post('/feriantes',[CoordinadorFerianteController::class,'store']);
    Route::get('/feriantes/{id}/editar',[CoordinadorFerianteController::class,'edit']);
    Route::patch('/feriantes/{id}',[CoordinadorFerianteController::class,'update']);
    Route::patch('/feriantes/{id}/estado', [CoordinadorFerianteController::class, 'cambiarEstado']);
    Route::delete('/feriantes/{id}', [CoordinadorFerianteController::class, 'destroy']);

Route::patch('/puestos/{id}/liberar', [PuestoController::class, 'liberar']);

    // Eventos: lugar, días, estado y ocupación de mesas
    Route::get('/eventos', [EventoController::class, 'index']);
    Route::post('/eventos', [EventoController::class, 'store']);
    Route::get('/eventos/{evento}', [EventoController::class, 'show']);
    Route::patch('/eventos/{evento}', [EventoController::class, 'update']);
    Route::delete('/eventos/{evento}', [EventoController::class, 'destroy']);

    // Diseño de la feria sobre el plano de cada lugar
    Route::get('/plano', [PlanoController::class, 'show']);
    Route::post('/secciones', [PlanoController::class, 'storeSeccion']);
    Route::patch('/secciones/{seccion}', [PlanoController::class, 'updateSeccion']);
    Route::delete('/secciones/{seccion}', [PlanoController::class, 'destroySeccion']);
    Route::post('/secciones/{seccion}/completar', [PlanoController::class, 'completarSeccion']);
    Route::post('/puestos', [PlanoController::class, 'storePuesto']);
    Route::patch('/puestos/{puesto}', [PlanoController::class, 'updatePuesto']);
    Route::delete('/puestos/{puesto}', [PlanoController::class, 'destroyPuesto']);
    Route::patch('/categorias/{categoria}', [PlanoController::class, 'updateCategoria']);

});
