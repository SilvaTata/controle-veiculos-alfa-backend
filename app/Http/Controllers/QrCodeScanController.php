<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Veiculo;
use App\Models\Solicitar; // Corrigido: Modelo é Solicitar, não Solicitacao
use App\Models\HistSolicitar; // Supondo que este modelo exista para o histórico
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon; // Mantenha se for usar para logs ou outras lógicas

class QrCodeScanController extends Controller
{
    public function handleScan(Request $request, Veiculo $veiculo) // Veiculo injetado via Route Model Binding
    {
        $user = Auth::user();

        // Validação básica do usuário (se não estiver usando middleware)
        if (!$user) {
             Log::warning("QR Scan attempt by unauthenticated user for Veiculo {$veiculo->id}.");
             return response()->json(['action' => 'error', 'message' => 'Usuário não autenticado.'], 401);
        }

        Log::info("QR Scan initiated: User {$user->id} ({$user->name}) scanned Veiculo {$veiculo->id} (Placa: {$veiculo->placa}, Status: {$veiculo->status_veiculo})");

        // --- CENÁRIO 1: VERIFICAR SE O USUÁRIO TEM RESERVA ACEITA E NÃO INICIADA PARA ESTE VEÍCULO ---
        $solicitacaoPropria = Solicitar::where('user_id', $user->id)
            ->where('veiculo_id', $veiculo->id)
            ->where('situacao', 'aceita') // Somente solicitações aceitas
            ->whereDoesntHave('historico', function ($subQuery) {
                // Garante que não existe um histórico com data_inicio preenchida para esta solicitação
                 $subQuery->whereNotNull('data_inicio');
            })
            ->orderBy('prev_data_inicio', 'asc') // Pega a mais próxima se houver múltiplas (raro)
            ->orderBy('prev_hora_inicio', 'asc')
            ->first();

        if ($solicitacaoPropria) {
            Log::info("QR Scan: Found active, unstarted request {$solicitacaoPropria->id} for user {$user->id} and scanned vehicle {$veiculo->id}.");

            // O usuário TEM uma reserva para ESTE veículo. Verificar o status REAL do veículo.
            switch ($veiculo->status_veiculo) {
                case 'reservado': // Status esperado quando reservado para este usuário
                case 'disponível': // Pode acontecer se o status não foi atualizado para 'reservado' ainda
                    Log::info("QR Scan: Vehicle status '{$veiculo->status_veiculo}' is OK for starting request {$solicitacaoPropria->id}. Action: allow_start.");
                    return response()->json([
                        'action' => 'allow_start', // Frontend: Habilitar início da viagem
                        'message' => "Veículo reservado para você! Placa {$veiculo->placa}. Pronto para iniciar a viagem.",
                        'solicitacao_id' => $solicitacaoPropria->id,
                        'veiculo' => $veiculo->load(['marca', 'modelo']) // Envia dados completos
                    ], 200);
                    break; // Boa prática incluir break

                case 'manutenção':
                    Log::warning("QR Scan: Vehicle {$veiculo->id} reserved by user {$user->id} (request {$solicitacaoPropria->id}) is under MAINTENANCE.");
                    return response()->json([
                        'action' => 'error', // Frontend: Mostrar erro
                        'message' => "Atenção! Seu veículo reservado (Placa: {$veiculo->placa}) encontra-se em manutenção. Por favor, contate a administração."
                    ], 409); // Conflict - o estado impede a ação
                    break;

                case 'em uso':
                     // Isso seria estranho. Indica um problema na lógica de status/reserva.
                     Log::error("QR Scan: CRITICAL! Vehicle {$veiculo->id} reserved by user {$user->id} (request {$solicitacaoPropria->id}) has status 'em uso'. Possible state inconsistency.");
                     return response()->json([
                         'action' => 'error',
                         'message' => "Erro inesperado. Seu veículo reservado (Placa: {$veiculo->placa}) consta como 'em uso'. Contate o suporte."
                     ], 500); // Internal Server Error - algo está errado
                     break;

                default:
                    Log::error("QR Scan: Vehicle {$veiculo->id} reserved by user {$user->id} (request {$solicitacaoPropria->id}) has unexpected status '{$veiculo->status_veiculo}'.");
                    return response()->json([
                        'action' => 'error',
                        'message' => "Estado inesperado ('{$veiculo->status_veiculo}') para seu veículo reservado (Placa: {$veiculo->placa}). Contate o suporte."
                    ], 500);
                    break;
            }
        }

        // --- Se chegou aqui, o usuário NÃO tem reserva ativa e não iniciada para ESTE veículo ---
        Log::info("QR Scan: No active, unstarted request found for user {$user->id} and scanned vehicle {$veiculo->id}. Checking other possibilities.");

        // --- CENÁRIO 2: VERIFICAR SE O USUÁRIO TEM RESERVA PARA OUTRO VEÍCULO ---
         $outraSolicitacaoAtiva = Solicitar::where('user_id', $user->id)
             // ->where('veiculo_id', '!=', $veiculo->id) // Removido: já sabemos que não é para este veículo
             ->where('situacao', 'aceita')
             ->whereDoesntHave('historico', function ($subQuery) {
                 $subQuery->whereNotNull('data_inicio');
             })
             ->orderBy('prev_data_inicio', 'asc')
             ->orderBy('prev_hora_inicio', 'asc')
             ->first();

         if ($outraSolicitacaoAtiva) {
             // Usuário tem reserva, mas para OUTRO veículo.
             $veiculoReservado = Veiculo::find($outraSolicitacaoAtiva->veiculo_id);
             $placaReservada = $veiculoReservado ? $veiculoReservado->placa : 'desconhecida';
             Log::warning("QR Scan: User {$user->id} scanned wrong vehicle {$veiculo->id} (Placa: {$veiculo->placa}). User has active request {$outraSolicitacaoAtiva->id} for vehicle {$outraSolicitacaoAtiva->veiculo_id} (Placa: {$placaReservada}).");
             return response()->json([
                 'action' => 'error', // Frontend: Mostrar erro
                 'message' => "Veículo errado! Você escaneou a placa {$veiculo->placa}, mas sua reserva ativa é para a placa {$placaReservada}."
             ], 409); // Conflict - tentou usar o veículo errado
         }


        // --- CENÁRIO 3: O USUÁRIO NÃO TEM NENHUMA RESERVA ATIVA. VERIFICAR O STATUS DO VEÍCULO ESCANEADO ---
        Log::info("QR Scan: User {$user->id} has no active requests. Evaluating scanned vehicle {$veiculo->id} (Status: {$veiculo->status_veiculo}) for immediate use.");

        switch ($veiculo->status_veiculo) {
            case 'disponível':
                // Veículo está livre para solicitação urgente
                Log::info("QR Scan: Vehicle {$veiculo->id} is available. Prompting user {$user->id} for urgent request. Action: prompt_urgent_request.");
                return response()->json([
                    'action' => 'prompt_urgent_request', // Frontend: Perguntar se quer solicitar
                    'message' => "Este veículo (Placa: {$veiculo->placa}) está disponível. Deseja criar uma solicitação de uso imediato?",
                    'veiculo_id' => $veiculo->id,
                    'veiculo' => $veiculo->load(['marca', 'modelo'])
                ], 200);
                break;

            case 'reservado':
                // Veículo está reservado, mas não por este usuário (já teríamos caído no primeiro 'if')
                Log::warning("QR Scan: Vehicle {$veiculo->id} scanned by user {$user->id} is RESERVED for another user.");
                // Tentar encontrar para quem está reservado (opcional, pode ser útil para logs ou admin)
                 $reservaExistente = Solicitar::where('veiculo_id', $veiculo->id)
                                            ->where('situacao', 'aceita')
                                            ->whereDoesntHave('historico', fn($q) => $q->whereNotNull('data_inicio'))
                                            ->first();
                 $reservadoPor = $reservaExistente ? "usuário ID {$reservaExistente->user_id}" : "outro usuário";

                return response()->json([
                    'action' => 'error', // Frontend: Mostrar erro
                    'message' => "Veículo (Placa: {$veiculo->placa}) indisponível. Encontra-se reservado para {$reservadoPor}."
                ], 409); // Conflict
                break;

            case 'em uso':
                // Veículo está em uso por outra pessoa
                 Log::warning("QR Scan: Vehicle {$veiculo->id} scanned by user {$user->id} is currently IN USE.");
                 // Opcional: Identificar quem está usando
                 $usoAtual = HistSolicitar::whereHas('solicitacao', fn($q) => $q->where('veiculo_id', $veiculo->id))
                                         ->whereNotNull('data_inicio')
                                         ->whereNull('data_final')
                                         ->with('solicitacao.user') // Carregar usuário que está usando
                                         ->first();
                 $emUsoPor = $usoAtual && $usoAtual->solicitacao->user ? "por {$usoAtual->solicitacao->user->name}" : "por outro usuário";

                return response()->json([
                    'action' => 'error', // Frontend: Mostrar erro
                    'message' => "Veículo (Placa: {$veiculo->placa}) indisponível. Já está {$emUsoPor}."
                ], 409); // Conflict
                break;

            case 'manutenção':
                // Veículo está em manutenção
                Log::warning("QR Scan: Vehicle {$veiculo->id} scanned by user {$user->id} is under MAINTENANCE.");
                return response()->json([
                    'action' => 'error', // Frontend: Mostrar erro
                    'message' => "Veículo (Placa: {$veiculo->placa}) indisponível. Encontra-se em manutenção."
                ], 409); // Conflict
                break;

            default:
                // Status desconhecido ou não esperado
                Log::error("QR Scan: Vehicle {$veiculo->id} scanned by user {$user->id} has unknown/unexpected status '{$veiculo->status_veiculo}'.");
                return response()->json([
                    'action' => 'error', // Frontend: Mostrar erro
                    'message' => "Veículo (Placa: {$veiculo->placa}) indisponível devido a um estado inesperado ('{$veiculo->status_veiculo}'). Contate o suporte."
                ], 500);
                break;
        }
    }
}