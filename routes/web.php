<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\FerianteController;
use App\Http\Controllers\PuestoController;
use App\Http\Controllers\CoordinadorController;
use App\Http\Controllers\MiCuentaController;
use App\Http\Controllers\FerianteAuthController;
use App\Http\Controllers\CoordinadorFerianteController;

//Públicas Feriantes//

Route::get('/',function(){
    $puestos =\App\Models\Puesto::with('feriante:id,nombre,apellido')->orderBy('numero')->get();

    return Inertia::render('Formulario',['puestos'=>$puestos]);

});



Route::post('/feriantes',[FerianteController::class,'store']);

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
});


// ==Login del coordinador== //

Route::get('/login',fn()=>Inertia::render('Login'))->name('login');
Route::post('/login',[CoordinadorController::class,'login']);
Route::post('/logout',[CoordinadorController::class, 'logout'])->middleware('auth');


//Panel coordinador-CRUD completo//

Route::Middleware('auth')->prefix('coordinador')->group(function(){
    Route::get('/',[CoordinadorController::class,'index']);


    Route::get('/feriantes/crear',[CoordinadorFerianteController::class,'create']);
    Route::post('/feriantes',[CoordinadorFerianteController::class,'store']);
    Route::get('/feriantes/{id}/editar',[CoordinadorFerianteController::class,'edit']);
    Route::patch('/feriantes/{id}',[CoordinadorFerianteController::class,'update']);
    Route::patch('/feriantes/{id}/estado', [CoordinadorFerianteController::class, 'cambiarEstado']);
    Route::delete('/feriantes/{id}', [CoordinadorFerianteController::class, 'destroy']);

Route::patch('/puestos/{id}/liberar', [PuestoController::class, 'liberar']);

});
