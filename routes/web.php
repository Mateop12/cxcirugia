<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\EstadoController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('/turnero/panel');
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth'])->name('dashboard');

require __DIR__.'/auth.php';

Auth::routes();

Route::middleware(['auth'])->group(function () {
    Route::resource('pacientes', PacienteController::class)->except(['index']);
});
// Route::resource('pacientes', PacienteController::class)->middleware('auth');

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

Route::redirect('/register', '/login');

Route::get('/registro-seguro-genezen', [App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');

Route::post('/registro-seguro-genezen', [App\Http\Controllers\Auth\RegisterController::class, 'register']);

// Rutas para áreas
Route::resource('areas', AreaController::class)->middleware('auth');

// Rutas para estados
Route::resource('estados', EstadoController::class)->middleware('auth');

Route::get('/sala-espera', [PacienteController::class, 'salaEspera'])->name('pacientes.salaEspera');

// --- SISTEMA DE TURNERO ---

// Rutas públicas (Pantalla TV)
Route::get('/turnero/tv', [App\Http\Controllers\TurneroController::class, 'pantallaTV'])->name('turnero.tv');
Route::get('/turnero/api/activos', [App\Http\Controllers\TurneroController::class, 'obtenerTurnosActivos'])->name('turnero.api.activos');

// Rutas protegidas (Panel de Control y Gestión)
Route::middleware(['auth'])->group(function () {
    // Panel de Control
    Route::get('/turnero/panel', [App\Http\Controllers\TurneroController::class, 'panelControl'])->name('turnero.panel');
    Route::post('/turnero/llamar', [App\Http\Controllers\TurneroController::class, 'llamarTurno'])->name('turnero.llamar');
    Route::post('/turnero/rellamar', [App\Http\Controllers\TurneroController::class, 'rellamarTurno'])->name('turnero.rellamar');
    Route::post('/turnero/asignar-destino', [App\Http\Controllers\TurneroController::class, 'asignarDestino'])->name('turnero.asignarDestino');
    Route::post('/turnero/generar/{paciente}', [App\Http\Controllers\TurneroController::class, 'generarTurno'])->name('turnero.generar');
    Route::post('/turnero/reiniciar', [App\Http\Controllers\TurneroController::class, 'reiniciarTurnos'])->name('turnero.reiniciar');
    
    // Gestión de Destinos
    Route::resource('destinos', App\Http\Controllers\DestinoController::class);

    // Módulo de Admisión
    Route::prefix('admision')->group(function () {
        Route::get('/', [App\Http\Controllers\AdmisionController::class, 'index'])->name('admision.index');
        Route::get('/create', [App\Http\Controllers\AdmisionController::class, 'create'])->name('admision.create');
        Route::post('/buscar', [App\Http\Controllers\AdmisionController::class, 'buscar'])->name('admision.buscar');
        Route::post('/ingresar', [App\Http\Controllers\AdmisionController::class, 'store'])->name('admision.store');
        Route::post('/asignar-turno/{paciente}', [App\Http\Controllers\AdmisionController::class, 'asignarTurno'])->name('admision.asignarTurno');
    });
});
