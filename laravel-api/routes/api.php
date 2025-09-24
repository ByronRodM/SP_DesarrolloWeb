<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TareaController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Usuario autenticado actual
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Auth (público): solo login
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

// Cambio de contraseña forzado para admins con contraseña por defecto
Route::middleware('auth:sanctum')->post('/change-password', [AuthController::class, 'changePassword']);

// CRUD de usuarios protegido (cumple requisito: 401 si no hay token válido)
Route::middleware('auth:sanctum')->prefix('usuarios')->group(function () {
    Route::get('/listUsers', [UsuarioController::class, 'index']);
    Route::post('/addUser', [UsuarioController::class, 'store']);
    Route::get('/getUser/{id}', [UsuarioController::class, 'show']);
    Route::put('/updateUser/{id}', [UsuarioController::class, 'update']);
    Route::delete('/deleteUser/{id}', [UsuarioController::class, 'destroy']);
});

// Rutas de tareas (requieren autenticación)
Route::middleware('auth:sanctum')->prefix('tareas')->group(function(){
    Route::get('/', [TareaController::class, 'index']);
    Route::post('/create', [TareaController::class, 'store']);
    Route::put('/{id}', [TareaController::class, 'update']);
    Route::get('/export/pending', [TareaController::class, 'exportPending']);
});
