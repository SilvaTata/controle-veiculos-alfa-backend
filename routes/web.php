<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\WebVeiculoController;
use App\Http\Controllers\WebSolicitarController;
use App\Http\Controllers\WebNotificationController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Auth;

Auth::routes();
Route::get('/', function () {
    return view('auth.login');
});

Route::get('/home', [HomeController::class, 'index'])->name('home');

//USUÁRIOS
Route::resource('teste', UsuarioController::class)->middleware('auth');

Route::get('teste.permissao/{id}', [UsuarioController::class, 'permissao'])->name('teste.permissao')->middleware('auth');

Route::post('/teste/{id}/mudarStatusU', [UsuarioController::class, 'mudarStatusU'])->name('teste.mudarStatusU')->middleware('auth');

//VEÍCULOS
Route::resource('veiculos', WebVeiculoController::class)->middleware('auth');

Route::post('/veiculos/{id}/mudarStatus', [WebVeiculoController::class, 'mudarStatus'])->name('veiculos.mudarStatus')->middleware('auth');

Route::post('/marcas', [WebVeiculoController::class, 'marca'])->name('marcas.store')->middleware('auth'); 

Route::post('/modelos', [WebVeiculoController::class, 'modelo'])->name('modelos.store')->middleware('auth');


//SOLICITAÇÕES:
Route::get('solicitar', [WebVeiculoController::class, 'solicitarIndex'])->name('solicitar.index')->middleware('auth');

Route::get('solicitar/create/{id}', [WebVeiculoController::class, 'solicitarCarro'])->name('solicitar.create')->middleware('auth');

Route::post('solicitar/store', [WebSolicitarController::class, 'store'])->name('solicitar.store')->middleware('auth');

Route::match(['get', 'post'], 'solicitar/{id}', [WebSolicitarController::class, 'index'])->name('solicitar.show')->middleware('auth');

Route::get('solicitar/ver/{id}', [WebSolicitarController::class, 'ver'])->name('solicitar.ver')->middleware('auth');

Route::get('solicitar/start/{id}', [WebSolicitarController::class, 'start'])->name('solicitar.start')->middleware('auth');

Route::post('solicitar/prosseguir/{id}', [WebSolicitarController::class, 'prosseguir'])->name('solicitar.prosseguir')->middleware('auth');

Route::get('solicitar/end/{id}', [WebSolicitarController::class, 'end'])->name('solicitar.end')->middleware('auth');

Route::post('solicitar/finalizar/{id}', [WebSolicitarController::class, 'finalizar'])->name('solicitar.finalizar')->middleware('auth');

Route::post('/solicitar/{id}/aceitar', [WebSolicitarController::class, 'aceitar'])->name('solicitar.aceitar')->middleware('auth');

Route::post('/solicitar/{id}/motivoRecusado', [WebSolicitarController::class, 'motivoRecusado'])->name('solicitar.motivoRecusado')->middleware('auth');

Route::get('/solicitar/{id}/recusar', [WebSolicitarController::class, 'recusar'])->name('solicitar.recusar')->middleware('auth');

Route::get('solicitar/finalizadas/{id}', [WebSolicitarController::class, 'finalizadas'])->name('solicitar.finalizadas')->middleware('auth');

Route::get('/gerar-pdf/{id}', [WebSolicitarController::class, 'gerarPDF'])->name('gerar.pdf')->middleware('auth');

Route::get('/exportar-excel/{id}', [WebSolicitarController::class, 'exportarExcel'])->name('exportar.excel')->middleware('auth');

Route::get('/exportar-todas-excel', [WebSolicitarController::class, 'exportarTodasExcel'])->name('exportar.todas.excel')->middleware('auth');

Route::get('/solicitar/recusadas/{id}', [WebSolicitarController::class, 'solicitacoesRecusadas'])->name('solicitar.solicitacoesRecusadas')->middleware('auth');

Route::get('solicitar/ver-recusadas/{id}', [WebSolicitarController::class, 'verRecusada'])->name('solicitar.verrecusada')->middleware('auth');


//NOTIFICAÇÕES
Route::get('/notificacoes', [WebNotificationController::class, 'index'])->name('solicitar.notificacoes');

Route::get('/notificacoes/contar', [WebNotificationController::class, 'count']);

Route::get('/notificacoes/listar', [WebNotificationController::class, 'list']);

Route::patch('/notificacoes/{id}/marcar-como-lida', [WebNotificationController::class, 'markAsRead'])->name('notificacoes.marcarComoLida');



Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
