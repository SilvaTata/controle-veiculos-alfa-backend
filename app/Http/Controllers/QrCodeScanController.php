<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Veiculo;
use App\Models\Solicitar;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon; 

class QrCodeScanController extends Controller
{
    public function handleScan(Request $request, Veiculo $veiculo)
    {
        $user = Auth::user();

        if (!$user) {
            Log::warning("QR Scan attempt failed: User not authenticated.");
            return response()->json(['error' => 'Não autenticado. Faça login primeiro.'], 401);
        }

        Log::info("QR Scan initiated: User {$user->id} scanned Veiculo {$veiculo->id}");

        $now = Carbon::now(); 

        $solicitacaoAgendada = Solicitar::where('user_id', $user->id)
            ->where('situacao', 'aceita')
            ->where(function ($query) use ($now) {
                $query->whereRaw("TIMESTAMP(prev_data_inicio, prev_hora_inicio) <= ?", [$now]) 
                      ->whereRaw("TIMESTAMP(prev_data_final, prev_hora_final) >= ?", [$now]);  
            })
            ->where(function ($query) {
                $query->whereDoesntHave('historico')
                      ->orWhereHas('historico', function ($subQuery) {
                          $subQuery->whereNull('data_inicio');
                      });
            })
            ->orderBy('prev_data_inicio', 'asc')
            ->orderBy('prev_hora_inicio', 'asc')
            ->first(); 


        if ($solicitacaoAgendada) {
            Log::info("QR Scan: User {$user->id} has a valid scheduled request: {$solicitacaoAgendada->id}");

          
            if ($solicitacaoAgendada->veiculo_id === $veiculo->id) {
                
                Log::info("QR Scan: Correct vehicle ({$veiculo->id}) for request {$solicitacaoAgendada->id}. Action: allow_start.");
                return response()->json([
                    'action' => 'allow_start', 
                    'message' => 'Este é o seu veículo agendado. Você pode iniciar a viagem.',
                    'solicitacao_id' => $solicitacaoAgendada->id,
                    'veiculo' => $veiculo->load(['marca', 'modelo'])
                ], 200); 
            } else {
                Log::warning("QR Scan: Incorrect vehicle scanned ({$veiculo->id}). User {$user->id} expected vehicle {$solicitacaoAgendada->veiculo_id} for request {$solicitacaoAgendada->id}. Action: error.");
                return response()->json([
                    'action' => 'error',
                    'message' => 'Este não é o veículo que você agendou para este horário. Verifique sua solicitação.'
                ], 409);
            }
        } else {
            Log::info("QR Scan: User {$user->id} has no active scheduled request for now. Checking status of scanned vehicle {$veiculo->id}.");

            $veiculo->refresh();

            if ($veiculo->status_veiculo === 'disponível') {
                Log::info("QR Scan: Vehicle {$veiculo->id} is available. Action: prompt_urgent_request.");
                return response()->json([
                    'action' => 'prompt_urgent_request',
                    'message' => 'Este veículo está disponível. Deseja criar uma solicitação de uso imediato (urgente)?',
                    'veiculo_id' => $veiculo->id, 
                    'veiculo' => $veiculo->load(['marca', 'modelo'])
                ], 200); 
            } else {
                Log::warning("QR Scan: Scanned vehicle {$veiculo->id} is not available (status: {$veiculo->status_veiculo}). Action: error.");
                return response()->json([
                    'action' => 'error',
                    'message' => 'Este veículo não está disponível no momento (Status: ' . $veiculo->status_veiculo . ').'
                ], 409); 
            }
        }
    }
}