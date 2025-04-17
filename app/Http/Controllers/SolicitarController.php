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

class SolicitarController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Usuário não autenticado.'], 401);
        }

        if ($user->cargo_id === null) {
            return response()->json(['error' => 'Cargo não encontrado.'], 404);
        }

        if ($user->cargo_id === 1) {
            $veiculos = Veiculo::where('status_veiculo', 'reservado', 'em uso')->get();
            $solicitacoes = Solicitar::all();
            if ($solicitacoes->isEmpty() || $veiculos->isEmpty()) {
                return response()->json(['error' => 'Nenhuma solicitação/veículo encontrada.'], 404);
            }

            return response()->json([
                'message' => 'Solicitações encontradas com sucesso.',
                'solicitacoes' => $solicitacoes->load(['user', 'veiculo']),

            ], 200);
        } else {
            $solicitadosDoUsuario = Veiculo::where('status_veiculo', 'reservado', 'em uso')
                ->whereHas('solicitars', function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                        ->where('situacao', 'aceita');
                })
                ->get();
            if ($solicitadosDoUsuario->isEmpty()) {
                return response()->json(['error' => 'Você não possui veículos em uso no momento.'], 404);
            }

            return response()->json($solicitadosDoUsuario, 200);
        }
    }

    public function show($id)
    {
        $solicitar = Solicitar::find($id);

        if (!$solicitar) {
            return response()->json(['error' => 'Solicitação não encontrada.'], 404);
        }

        return response()->json($solicitar, 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'veiculo_id' => 'required|exists:veiculos,id',
            'prev_hora_inicio' => 'required|date_format:H:i',
            'prev_data_inicio' => 'required|date_format:Y-m-d',
            'prev_hora_final' => 'required|date_format:H:i|after:prev_hora_inicio',
            'prev_data_final' => 'required|date_format:Y-m-d|after_or_equal:prev_data_inicio',
            'motivo' => 'required|string|max:255',
        ]);

        $data['user_id'] = Auth::id();
        $data['situacao'] = 'pendente';

        DB::beginTransaction();
        try {
            $veiculo = Veiculo::findOrFail($data['veiculo_id']);

            if ($veiculo->status_veiculo === 'manutenção') {
                DB::rollBack();
                return response()->json(['error' => 'Veículo está em manutenção e não pode ser solicitado.'], 409);
            }

            $solicitar = Solicitar::create($data);

            DB::commit();
 
            return response()->json([
                'message' => 'Solicitação criada com sucesso e está pendente de aprovação.',
                'solicitacao' => $solicitar,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar solicitação: ' . $e->getMessage());
            return response()->json([
                'error' => 'Erro ao criar solicitação.',
                'message' => $e->getMessage()], 500);
        }  
    }

    public function aceitarOuRecusar(Request $request, $id)
    {
        $user = Auth::user();

        if ($user->cargo_id !== 1) {
            return response()->json(['error' => 'Acesso não autorizado.'], 403);
        }

        $solicitar = Solicitar::find($id);

        if (!$solicitar) {
            return response()->json(['error' => 'Solicitação não encontrada.'], 404);
        }

        if ($solicitar->situacao !== 'pendente') {
            return response()->json(['error' => 'Solicitação já processada.'], 400);
        }

        $validated = $request->validate([
            'button' => 'required|in:aceitar,recusar',
            'motivo_recusa' => 'required_if:button,recusar|nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            if ($validated['button'] === 'aceitar') {

                $veiculo = $solicitar->veiculo;

                 if ($veiculo->status_veiculo === 'manutenção') {
                     DB::rollBack();
                     return response()->json(['error' => 'Não é possível aceitar. O veículo está em manutenção.'], 409);
                 }

                 if ($veiculo->status_veiculo === 'em uso') {
                    DB::rollBack();
                    return response()->json(['error' => 'Não é possível aceitar. O veículo já está em uso.'], 409);
                }
                 if ($veiculo->status_veiculo === 'reservado' && $solicitar->veiculo_id == $veiculo->id) {
                    DB::rollBack();
                    return response()->json(['error' => 'Não é possível aceitar. O veículo já está reservado.'], 409);
                }

                $veiculo->status_veiculo = 'reservado';
                $veiculo->save();

                $solicitar->situacao = 'aceita';
                $solicitar->adm_id = $user->id;
                $solicitar->save();
                
                HistSolicitar::updateOrCreate(
                    ['solicitacao_id' => $solicitar->id],
                    [
                        'hora_aceito' => now()->format('H:i'),
                        'data_aceito' => now()->format('Y-m-d'),
                        'adm_id' => $user->id,
                    ]
                    );

                DB::commit();
                return response()->json(['message' => 'Solicitação aceita com sucesso.'], 200);

        } elseif ($validated['button'] === 'recusar') {

            $solicitar->situacao = 'recusada';
            $solicitar->motivo_recusa = $validated['motivo_recusa'];
            $solicitar->hora_recusa = now()->format('H:i');
            $solicitar->data_recusa = now()->format('Y-m-d');
            $solicitar->adm_id = $user->id;
            $solicitar->save();

            DB::commit();
            return response()->json(['message' => 'Solicitação recusada com sucesso.'], 200);
        }

        DB::rollBack();
        return response()->json(['error' => 'Ação inválida.'], 400);
        
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("Erro ao processar aceitar/recusar solicitação {$id}: " . $e->getMessage());
        return response()->json([
            'error' => 'Erro ao processar a solicitação.',
            'message' => $e->getMessage()
        ], 500);
    }
    }   
    
    public function iniciar(Request $request, $id) 
    {
        $user = Auth::user();

        $solicitar = Solicitar::find($id);

        $veiculo = $solicitar->veiculo;

        if (!$solicitar) {
            return response()->json(['error' => 'Solicitação não encontrada.'], 404);
        }
        
        if($user->id !== $solicitar->user_id) {
            return response()->json(['error' => 'Acesso não autorizado.'], 403);
        }

        if($solicitar->situacao !== 'aceita') {
            return response()->json(['error' => 'Solicitação não está aceita ou já foi procesada.'], 400);
        }

        $histSolicitacao = HistSolicitar::where('solicitacao_id', $solicitar->id)->first();
        if ($histSolicitacao || $histSolicitacao-> data_inicio !==null) {
            return response()->json(['error' => 'Veículo já retirado.'], 409);
        }
        
        $validated = $request->validate([
            'placa_confirmar' =>'required|string',
            'km_velocimetro' => 'required|integer|min:0',
        ]);
        
        if ('placa_confirmar' != $veiculo->placa) {
            return response()->json(['error' => 'Placa informada não confere com a do veículo.'], 400);
        }

        DB::beginTransaction();
        try{
            $now = now();
            $data_inicio = $now->format('Y-m-d');
            $hora_inicio = $now->format('H:i:s');

            $histSolicitacao = HistSolicitar::update(
                ['solicitacao_id' => $solicitar->id],
                [
                    'hora_inicio' => $hora_inicio,
                    'data_inicio' => $data_inicio,
                    'adm_id' => $histSolicitacao->adm_id ?? $solicitar->adm_id,
                ]
                );

            $histVeiculo = HistVeiculo::updateOrCreate(
                ['solicitacao_id' => $solicitar->id],
                [
                    'veiculo_id' => $veiculo->id,
                    'km_inicio' => $validated['km_velocimetro'],
                    'km_final' => 0,
                ]
            );

            $veiculo->stauts_veiculo = 'em uso';
            $veiculo->save();

            DB::commit();

            Log::info("Solicitação {$id} iniciada com sucesso por {$user->id}. Veículo {$veiculo->id} está 'em uso'.");
            
            return response()->json([
                'message' => 'Veículo retirado com sucesso.',
                'solicitacao' => $solicitar,
                'hora_inicio' => $hora_inicio,
                'data_inicio' => $data_inicio,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erro ao iniciar uso do veículo: " . $e->getMessage());
            return response()->json([
                'error' => 'Erro ao iniciar uso do veículo.',
                'message' => $e->getMessage()
            ], 500);
    }
    }

    public function finalizar(Request $request, $id) 
    {
        $user = Auth::user();

        $solicitar = Solicitar::with('veiculo')->find($id);

        if (!$solicitar) {
            return response()->json(['error' => 'Solicitação não encontrada.'], 404);
        }
        
        if($user->id !== $solicitar->user_id) {
            return response()->json(['error' => 'Acesso não autorizado.'], 403);
        }
        
        $histSolicitacao = HistSolicitar::where('solicitacao_id', $solicitar->id)->first();
        if ($histSolicitacao || $histSolicitacao->data_inicio === null) {
            return response()->json(['error' => 'Veículo ainda não foi retirado.'], 400);
        }

        if ($solicitar->situacao === 'concluída' || $solicitar->situacao === 'recusada' || $solicitar->situacao === 'pendente') {
            return response()->json(['error' => 'Solicitação não está em um estado que permita finalização.'], 400);
        }

        $veiculo = $solicitar->veiculo;

        $validated = $request->validate([
            'placa_confirmar' =>'required|string',
            'km_velocimetro' => 'required|integer|min:0',
            'obs_users' => 'nullable|string|max:255',
        ]);


        if ('placa_confirmar' != $veiculo->placa) {
            return response()->json(['error' => 'Placa informada não confere com a do veículo.'], 400);
        }

        DB::beginTransaction();
        try{
            $histVeiculo = HistVeiculo::where('solicitacao_id', $solicitar->id)->first();

            if (!$histVeiculo) {
                DB::rollBack();
                return response()->json(['error' => 'Histórico não encontrado.'], 404);
            }

            $km_final = $validated['km_velocimetro'];
            if ($km_final < $histVeiculo->km_inicio) {
                DB::rollBack();
                return response()->json(['error' => "KM final informado ({$km_final}) é menor que o KM inicial. Verifique o velocimetro."], 400);
            }
            $histVeiculo->km_final = $km_final;
            $histVeiculo->km_gasto = $km_final - $histVeiculo->km_inicio;
            $histVeiculo->save();
            

            $now = now();
            $histSolicitacao->date_final = $now->format('Y-m-d');
            $histSolicitacao->hora_final = $now->format('H:i:s');
            if ($validated['obs_users']) {
                $histSolicitacao->obs_users = $validated['obs_users'];
            }
            $histSolicitacao->save();

            $solicitar->situacao = 'concluída';
            $solicitar->save();

            $veiculo->status_veiculo = 'disponível';
            $veiculo->save();



            DB::commit();

            Log::info("Solicitação {$id} finalizada com sucesso pelo usuário {$user->id}. Veículo {$veiculo->id} agora está 'disponível'. KM gasto: " . ($km_final - $histVeiculo->km_inicio));
            
            return response()->json([
                'message' => 'Veículo devolvido com sucesso.',
                'solicitacao' => $solicitar,
                'km_rodado' => $km_final - ($histVeiculo->km_inicio),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erro ao finalizar solicitação {$id}: " . $e->getMessage());
            return response()->json([
                'error' => 'Erro ao finalizar uso do veículo.',
                'message' => $e->getMessage()
            ], 500);
    }
    }
}
