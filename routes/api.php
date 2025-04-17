<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VeiculoController;
use App\Http\Controllers\SolicitarController;
use App\Http\Controllers\QrCodeScanController;

Route::get('/users', [AuthController::class, 'users']);

Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(
    function () {
        Route::get('/qrcode/scan/{veiculo}', [QrCodeScanController::class, 'handleScan'])->name('api.qrcode.scan');
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/veiculos', [VeiculoController::class, 'index']);
        Route::post('/veiculo/create', [VeiculoController::class, 'store']);
        Route::get('/veiculo/{id}', [VeiculoController::class, 'show']);
        Route::put('/veiculo/update/{id}', [VeiculoController::class, 'update']);
        Route::delete('/veiculos/{id}', [VeiculoController::class, 'delete']);
        Route::get('/veiculos/disponiveis', [VeiculoController::class, 'disponivel']);
        Route::get('veiculos/solicitados', [VeiculoController::class, 'solicitados']);
        Route::get('solicitacoes', [SolicitarController::class, 'index']);
        Route::post('solicitar/create', [SolicitarController::class, 'store']);
        Route::get('solicitar/{id}', [SolicitarController::class, 'show']);
        Route::post('solicitar/{id}/aceitarOuRecusar', [SolicitarController::class, 'aceitarOuRecusar']);
        Route::post('solicitar/{id}/iniciar', [SolicitarController::class, 'iniciar']);
        Route::post('solicitar/{id}/finalizar', [SolicitarController::class, 'finalizar']);
    }
);