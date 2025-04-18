<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Veiculo;
use App\Models\Solicitar;
use App\Models\HistSolicitar;
use App\Models\HistVeiculo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class SolicitarController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) { return response()->json(['error' => 'Usuário não autenticado.'], 401); }
        if ($user->cargo_id === null) { return response()->json(['error' => 'Cargo não encontrado.'], 404); }

        if ($user->cargo_id === 1) {
             $solicitacoes = Solicitar::whereIn('situacao', ['aceita', 'pendente']) // 'em_andamento' se usar
                 ->with(['user', 'veiculo'])
                 ->orderBy('prev_data_inicio', 'asc')->orderBy('prev_hora_inicio', 'asc')->get();
            return response()->json(['message' => $solicitacoes->isEmpty() ? 'Nenhuma solicitação ativa encontrada.' : 'Solicitações encontradas com sucesso.', 'solicitacoes' => $solicitacoes], 200);
        } else {
             $solicitacoesDoUsuario = Solicitar::where('user_id', $user->id)
                 ->whereIn('situacao', ['aceita', 'pendente']) 
                 ->with(['veiculo.marca', 'veiculo.modelo'])
                 ->orderBy('prev_data_inicio', 'asc')->orderBy('prev_hora_inicio', 'asc')->get();
             return response()->json(['message' => $solicitacoesDoUsuario->isEmpty() ? 'Você não possui solicitações ativas.' : 'Suas solicitações ativas.', 'solicitacoes' => $solicitacoesDoUsuario], 200);
        }
    }

    public function show($id)
    {
        $solicitar = Solicitar::with(['user','veiculo.marca','veiculo.modelo','histSolicitar','histVeiculo'])->find($id);
        if (!$solicitar) { return response()->json(['error' => 'Solicitação não encontrada.'], 404); }
        $user = Auth::user();
        if ($user->cargo_id !== 1 && $solicitar->user_id !== $user->id) { return response()->json(['error' => 'Acesso não autorizado.'], 403); }
        return response()->json(['message' => 'Busca realizada com sucesso', 'solicitar' => $solicitar], 200);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'veiculo_id' => 'required|exists:veiculos,id',
            'prev_hora_inicio' => 'required_without:urgente|date_format:H:i',
            'prev_data_inicio' => 'required_without:urgente|date_format:Y-m-d',
            'prev_hora_final' => 'required_without:urgente|date_format:H:i|after:prev_hora_inicio',
            'prev_data_final' => 'required_without:urgente|date_format:Y-m-d|after_or_equal:prev_data_inicio',
            'motivo' => 'required_if:urgente,true|nullable|string|max:500',
            'km_inicial' => 'required_if:urgente,true|nullable|integer|min:0',
            'urgente' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) { return response()->json(['errors' => $validator->errors()], 422); }

        DB::beginTransaction();
        try {
            $veiculo = Veiculo::findOrFail($request->input('veiculo_id'));
            $isUrgent = $request->input('urgente', false);

            if ($isUrgent) {
                Log::info("Processing URGENT request by User {$user->id} for Vehicle {$veiculo->id}");
                if ($veiculo->status_veiculo !== 'disponível') {
                    DB::rollBack();
                    Log::warning("Urgent request failed: Vehicle {$veiculo->id} not available (Status: {$veiculo->status_veiculo}).");
                    return response()->json(['message' => "Veículo (Placa: {$veiculo->placa}) não está disponível."], 409);
                }

                $now = Carbon::now();
                $solicitar = Solicitar::create([
                    'user_id' => $user->id,
                    'veiculo_id' => $veiculo->id,
                    'situacao' => 'aceita',
                    'motivo' => $request->input('motivo'),
                    'prev_data_inicio' => $now->toDateString(),
                    'prev_hora_inicio' => $now->toTimeString('minutes'),
                ]);
                Log::info("Urgent request {$solicitar->id} created in solicitars table.");

                HistSolicitar::create([
                    'solicitacao_id' => $solicitar->id,
                    'urgente' => true,         
                    'data_aceito' => $now,     
                    'hora_aceito' => $now->toTimeString('minutes'),
                    'data_inicio' => $now,     
                    'hora_inicio' => $now->toTimeString('minutes'),
                    'adm_id' => $user->id,    
                ]);
                Log::info("HistSolicitar created for urgent request {$solicitar->id}.");

                HistVeiculo::create([
                    'solicitacao_id' => $solicitar->id,
                    'veiculo_id' => $veiculo->id,
                    'km_inicio' => $request->input('km_inicial'), 
                    'km_final' => 0, 
                ]);
                Log::info("HistVeiculo created for request {$solicitar->id}.");

                $veiculo->status_veiculo = 'em uso';
                $veiculo->save();
                Log::info("Vehicle {$veiculo->id} status updated to 'em uso'.");

                DB::commit();
                return response()->json(['message' => 'Solicitação urgente criada e viagem iniciada!','solicitacao' => $solicitar->load('veiculo')], 201);

            }
            else {
                Log::info("Processing NORMAL request by User {$user->id} for Vehicle {$veiculo->id}");
                if ($veiculo->status_veiculo === 'manutenção') {
                    DB::rollBack();
                    return response()->json(['error' => 'Veículo está em manutenção.'], 409);
                }

                $solicitar = Solicitar::create([
                    'user_id' => $user->id,
                    'veiculo_id' => $veiculo->id,
                    'situacao' => 'pendente',
                    'motivo' => $request->input('motivo'),
                    'prev_data_inicio' => $request->input('prev_data_inicio'),
                    'prev_hora_inicio' => $request->input('prev_hora_inicio'),
                    'prev_data_final' => $request->input('prev_data_final'),
                    'prev_hora_final' => $request->input('prev_hora_final'),
                ]);
                Log::info("Normal request {$solicitar->id} created with status 'pendente'.");
                DB::commit();
                return response()->json(['message' => 'Solicitação criada e pendente de aprovação.','solicitacao' => $solicitar], 201);
            }

        } catch (\Exception $e) {
             DB::rollBack();
             Log::error('Erro ao criar solicitação: ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
             Log::error($e->getTraceAsString());
             return response()->json(['error' => 'Erro interno ao processar a solicitação.','message' => $e->getMessage()], 500);
        }
    }


     public function aceitarOuRecusar(Request $request, $id)
     {
         $user = Auth::user(); 
         if ($user->cargo_id !== 1) { return response()->json(['error' => 'Acesso não autorizado.'], 403); }

         $solicitar = Solicitar::find($id);
         if (!$solicitar) { return response()->json(['error' => 'Solicitação não encontrada.'], 404); }
         if ($solicitar->situacao !== 'pendente') { return response()->json(['error' => 'Solicitação não está mais pendente.'], 409); }

         $validated = $request->validate(['button' => 'required|in:aceitar,recusar','motivo_recusa' => 'required_if:button,recusar|nullable|string|max:255']);

         DB::beginTransaction();
         try {
             if ($validated['button'] === 'aceitar') {
                 Log::info("Admin {$user->id} ACCEPTING request {$solicitar->id}");
                 $veiculo = $solicitar->veiculo;
                 if (!in_array($veiculo->status_veiculo, ['disponível', 'reservado'])) {
                      DB::rollBack();
                      Log::warning("Cannot accept request {$solicitar->id}: Vehicle {$veiculo->id} status is '{$veiculo->status_veiculo}'.");
                      return response()->json(['error' => "Não é possível aceitar. O veículo está '{$veiculo->status_veiculo}'."], 409);
                  }

                 $solicitar->situacao = 'aceita';
                 $solicitar->adm_id = $user->id; 
                 $solicitar->save();
                 Log::info("Request {$solicitar->id} status updated to 'aceita'.");

                 HistSolicitar::updateOrCreate(
                     ['solicitacao_id' => $solicitar->id],
                     [
                         'urgente' => false, 
                         'data_aceito' => now()->toDateString(), 
                         'hora_aceito' => now()->toTimeString('minutes'), 
                         'adm_id' => $user->id,
                     ]
                 );
                  Log::info("HistSolicitar created/updated for accepted request {$solicitar->id}.");

                 if ($veiculo->status_veiculo === 'disponível') {
                     $veiculo->status_veiculo = 'reservado';
                     $veiculo->save();
                      Log::info("Vehicle {$veiculo->id} status updated to 'reservado'.");
                 }

                 DB::commit();
                 return response()->json(['message' => 'Solicitação aceita com sucesso.'], 200);

             } elseif ($validated['button'] === 'recusar') {
                  Log::info("Admin {$user->id} REJECTING request {$solicitar->id}");
                  $solicitar->situacao = 'recusada';
                  $solicitar->motivo_recusa = $validated['motivo_recusa'];
                  $solicitar->adm_id = $user->id; 
                  $solicitar->data_recusa = now()->toDateString();
                  $solicitar->hora_recusa = now()->toTimeString('minutes');
                  $solicitar->save();
                  Log::info("Request {$solicitar->id} status updated to 'recusada'.");
                  DB::commit();
                  return response()->json(['message' => 'Solicitação recusada com sucesso.'], 200);
             }
             DB::rollBack(); return response()->json(['error' => 'Ação inválida.'], 400);

         } catch (\Exception $e) {
             DB::rollBack();
             Log::error("Erro ao processar aceitar/recusar solicitação {$id}: " . $e->getMessage());
             Log::error($e->getTraceAsString());
             return response()->json(['error' => 'Erro interno ao processar a solicitação.','message' => $e->getMessage()], 500);
         }
     }

    public function iniciar(Request $request, $id)
    {
        $user = Auth::user();
        $solicitar = Solicitar::find($id);
        if (!$solicitar) { return response()->json(['error' => 'Solicitação não encontrada.'], 404); }

        $veiculo = $solicitar->veiculo;
        if ($user->id !== $solicitar->user_id) { return response()->json(['error' => 'Acesso não autorizado.'], 403); }
        if ($solicitar->situacao !== 'aceita') { return response()->json(['error' => 'Solicitação não está aceita.'], 409); }

        $histSolicitacao = HistSolicitar::where('solicitacao_id', $solicitar->id)->first();
        if ($histSolicitacao && $histSolicitacao->data_inicio !== null) { return response()->json(['error' => 'Viagem já iniciada.'], 409); }

        $validator = Validator::make($request->all(), ['placa_confirmar' => 'required|string','km_velocimetro' => 'required|integer|min:0']);
        if ($validator->fails()) { return response()->json(['errors' => $validator->errors()], 422); }

        $placaRequestNormalizada = strtoupper(str_replace('-', '', $request->input('placa_confirmar')));
        $placaVeiculoNormalizada = strtoupper(str_replace('-', '', $veiculo->placa));
        if ($placaRequestNormalizada !== $placaVeiculoNormalizada) { return response()->json(['message' => "Placa informada não confere."], 409); }

        DB::beginTransaction();
        try {
            $now = Carbon::now();
            $kmInicial = $request->input('km_velocimetro');

            $histSoli = HistSolicitar::updateOrCreate(
                 ['solicitacao_id' => $solicitar->id], 
                 [ 
                     'data_inicio' => $now,
                     'hora_inicio' => $now->toTimeString('minutes'),
                     'urgente' => false, 
                 ]
            );
            Log::info("HistSolicitar updated/created for started request {$solicitar->id}.");

             HistVeiculo::create([
                 'solicitacao_id' => $solicitar->id,
                 'veiculo_id' => $veiculo->id,
                 'km_inicio' => $kmInicial, 
                 'km_final' => 0,
             ]);
             Log::info("HistVeiculo created for request {$solicitar->id}.");

            $veiculo->status_veiculo = 'em uso';
            $veiculo->save();
            Log::info("Vehicle {$veiculo->id} status updated to 'em uso'.");


            DB::commit();
            Log::info("Request {$id} started successfully by User {$user->id}.");
            return response()->json(['message' => 'Viagem iniciada com sucesso!'], 200);

        } catch (\Exception $e) {
             DB::rollBack();
             Log::error("Error starting trip for Request {$id}: " . $e->getMessage());
             Log::error($e->getTraceAsString());
             return response()->json(['error' => 'Erro interno ao iniciar a viagem.','message' => $e->getMessage()], 500);
        }
    }

    public function finalizar(Request $request, $id)
    {
        $user = Auth::user();
        $solicitar = Solicitar::with('veiculo')->find($id);
        if (!$solicitar) { return response()->json(['error' => 'Solicitação não encontrada.'], 404); }
        if ($user->id !== $solicitar->user_id) { return response()->json(['error' => 'Acesso não autorizado.'], 403); }

        $histSolicitacao = HistSolicitar::where('solicitacao_id', $solicitar->id)->first();
        if (!$histSolicitacao || $histSolicitacao->data_inicio === null) { return response()->json(['error' => 'Viagem não iniciada.'], 409); }
        if ($histSolicitacao->data_final !== null) { return response()->json(['error' => 'Viagem já finalizada.'], 409); }

        $validator = Validator::make($request->all(), ['placa_confirmar' => 'required|string','km_velocimetro' => 'required|integer|min:0','obs_users' => 'nullable|string|max:500']);
        if ($validator->fails()) { return response()->json(['errors' => $validator->errors()], 422); }

        $veiculo = $solicitar->veiculo;
        $placaRequestNormalizada = strtoupper(str_replace('-', '', $request->input('placa_confirmar')));
        $placaVeiculoNormalizada = strtoupper(str_replace('-', '', $veiculo->placa));
        if ($placaRequestNormalizada !== $placaVeiculoNormalizada) { return response()->json(['message' => "Placa informada não confere."], 409); }

        $histVeiculo = HistVeiculo::where('solicitacao_id', $solicitar->id)->first();
        if (!$histVeiculo) {
             Log::error("Critical: HistVeiculo not found for request {$solicitar->id} during finalization!");
             return response()->json(['error' => 'Erro interno: dados de KM inicial não encontrados.'], 500);
        }
        $kmInicial = $histVeiculo->km_inicio;
        $kmFinal = $request->input('km_velocimetro');
        if ($kmFinal < $kmInicial) { return response()->json(['message' => "KM final ({$kmFinal}) menor que o inicial ({$kmInicial})."], 400); }

        DB::beginTransaction();
        try {
            $now = Carbon::now();
            $kmGasto = $kmFinal - $kmInicial;

            $histSolicitacao->data_final = $now;
            $histSolicitacao->hora_final = $now->toTimeString('minutes');
            $histSolicitacao->obs_users = $request->input('obs_users');
            $histSolicitacao->save();
            Log::info("HistSolicitar updated for finalized request {$solicitar->id}.");

            $histVeiculo->km_final = $kmFinal; 
            $histVeiculo->save();
            Log::info("HistVeiculo updated for request {$solicitar->id}. KM spent: {$kmGasto}");

            $solicitar->situacao = 'concluída';
            $solicitar->save();
            Log::info("Request {$solicitar->id} status updated to 'concluída'.");

            $veiculo->status_veiculo = 'disponível';
            $veiculo->km_atual = $kmFinal;
            $veiculo->km_revisao = $veiculo->km_revisao - $kmGasto;
            if ($veiculo->km_revisao <= 0) {
                $veiculo->km_revisao = 10000; 
                $veiculo->status_veiculo = 'manutenção'; 
            }
            $veiculo->save();
            Log::info("Vehicle {$veiculo->id} status updated to '{$veiculo->status_veiculo}'.");

            DB::commit();
            Log::info("Request {$id} finalized successfully by User {$user->id}.");
            return response()->json(['message' => 'Veículo devolvido com sucesso.','km_rodado' => $kmGasto], 200);

        } catch (\Exception $e) {
             DB::rollBack();
             Log::error("Error finalizing request {$id}: " . $e->getMessage());
             Log::error($e->getTraceAsString());
             return response()->json(['error' => 'Erro interno ao finalizar a viagem.','message' => $e->getMessage()], 500);
        }
    }
}