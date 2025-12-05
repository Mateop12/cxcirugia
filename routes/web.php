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
    return redirect('/login');
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth'])->name('dashboard');

require __DIR__.'/auth.php';

Auth::routes();

Route::middleware(['auth'])->group(function () {
    Route::resource('pacientes', PacienteController::class);
});
Route::resource('pacientes', PacienteController::class)->middleware('auth');

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
