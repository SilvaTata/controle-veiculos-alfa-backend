<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Veiculo;
use App\Models\Marca;
use App\Models\Modelo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Necessário para obter usuário logado no store (se for adaptar o store da API)
use Illuminate\Support\Facades\DB; // Necessário para transação no store (se for adaptar o store da API)
use Illuminate\Support\Facades\Log; // Necessário para log no store (se for adaptar o store da API)
use Illuminate\Validation\Rule; // Necessário para validação de status e unique ignore
use SimpleSoftwareIO\QrCode\Facades\QrCode; //extensão para o Qr Code

class WebVeiculoController extends Controller
{
    public function index(Request $request)
    {
        // Não precisa buscar marcas/modelos aqui se não for usar para filtrar
        // $marcas = Marca::orderBy('marca')->get();
        // $modelos = Modelo::orderBy('modelo')->get();

        $query = Veiculo::query()->with(['marca', 'modelo']); // Eager load relationships

        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('placa', 'like', $searchTerm)
                  ->orWhere('chassi', 'like', $searchTerm)
                  // Busca em tabelas relacionadas
                  ->orWhereHas('marca', function ($subQuery) use ($searchTerm) {
                      $subQuery->where('marca', 'like', $searchTerm);
                  })
                  ->orWhereHas('modelo', function ($subQuery) use ($searchTerm) {
                      $subQuery->where('modelo', 'like', $searchTerm);
                  });
            });
        }

        $veiculos = $query->orderBy('id', 'desc')->paginate(15); // Adicionado paginação como exemplo
        return view('veiculos.index', compact('veiculos')); // Passa só os veículos
    }

    public function create()
    {
        // Precisa passar marcas e modelos para o formulário de criação
        $marcas = Marca::orderBy('marca')->get();
        $modelos = Modelo::orderBy('modelo')->get();
        return view('veiculos.create', compact('marcas', 'modelos'));
    }

    public function store(Request $request)
    {
        // Validação adaptada para os novos campos e relacionamentos
        $validation = $request->validate([
            'ano' => 'required|integer|digits:4',
            'marca_id' => 'required|exists:marcas,id', 
            'modelo_id' => 'required|exists:modelos,id', 
            'placa' => 'required|string|max:10|unique:veiculos,placa', // Ajustado max length se necessário
            'cor' => 'required|string|max:30',
            'chassi' => 'required|string|size:17|unique:veiculos,chassi', // Usar size para tamanho exato
            'capacidade' => 'required|integer', // Removido max:20 se não for regra de negócio
            'km_atual' => 'required|integer|min:0',
            'km_revisao' => 'required|integer|min:0',
            'obs_veiculo' =>'nullable|string', // Renomeado de observacao
            'status_veiculo' => [ // Valida o enum status_veiculo
                'required',
                 Rule::in(['disponível', 'reservado', 'em uso', 'manutenção']),
            ]
        ]);

        // Remover campos que não existem mais na validação antes de criar
        // Ex: 'funcionamento' foi substituído por 'status_veiculo'

        DB::beginTransaction();
        try {
            // Criação usando os nomes corretos dos campos validados
            $veiculo = Veiculo::create([
                'ano' => $validation['ano'],
                'marca_id' => $validation['marca_id'],
                'modelo_id' => $validation['modelo_id'],
                'placa' => $validation['placa'],
                'cor' => $validation['cor'],
                'chassi' => $validation['chassi'],
                'capacidade' => $validation['capacidade'],
                'km_atual' => $validation['km_atual'],
                'km_revisao' => $validation['km_revisao'],
                'obs_veiculo' => $validation['obs_veiculo'],
                'status_veiculo' => $validation['status_veiculo'],
            ]);

            $qrCodeUrl = route('api.qrcode.scan', $veiculo->id); 
            $qrcodeContent = QrCode::format('svg')
                ->size(200)                 
                ->margin(1) 
                ->errorCorrection('L') 
                ->generate($qrCodeUrl); 

            $fileName = 'veiculo_' . $veiculo->id . '_' . time() . '.svg';
            $filePath = public_path('qrcodes/' . $fileName);

            if (file_put_contents($filePath, $qrcodeContent) === false) {
                Log::error("Erro ao salvar QR code para Veiculo ID: {$veiculo->id} em {$filePath}");
                DB::rollBack();
                return redirect()->back()->with('error', 'Erro ao gerar o QR Code do veículo. Tente novamente.')->withInput();
            }

            $veiculo->update(['qr_code' => $fileName]);

            DB::commit();

            return redirect()->route('veiculos.index')->with('success', 'Veículo criado com sucesso!'); 

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erro ao criar veículo: " . $e->getMessage()); 
            return redirect()->back()->with('error', 'Erro ao criar o veículo. Detalhes: ' . $e->getMessage())->withInput();
        }
    }
    public function marca(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
        ]);

        Marca::create(['marca' => $request->nome]);

        return redirect()->back()->with('success', 'Marca cadastrada com sucesso!');
    }

    public function modelo(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
        ]);

        Modelo::create(['modelo' => $request->nome]);

        return redirect()->back()->with('success', 'Modelo cadastrado com sucesso!');
    }

    public function show($id)
    {
        $veiculo = Veiculo::with(['marca', 'modelo'])->findOrFail($id);
        return view('veiculos.show', compact('veiculo'));
    }

    public function edit($id)
    {
        $veiculo = Veiculo::findOrFail($id); 
        $marcas = Marca::orderBy('marca')->get();
        $modelos = Modelo::orderBy('modelo')->get();
        return view('veiculos.edit', compact('veiculo', 'marcas', 'modelos'));
    }

    public function update(Request $request, $id)
    {
        $veiculo = Veiculo::findOrFail($id); 

        // Validação adaptada para atualização
        $validatedData = $request->validate([
            'ano' => 'required|integer|digits:4',
            'marca_id' => 'required|exists:marcas,id', 
            'modelo_id' => 'required|exists:modelos,id', 
            'placa' => [
                'required',
                'string',
                'max:10',
                 Rule::unique('veiculos', 'placa')->ignore($veiculo->id),
            ],
            'cor' => 'required|string|max:30',
            'chassi' => [ 
                'required',
                'string',
                'size:17',
                 Rule::unique('veiculos', 'chassi')->ignore($veiculo->id),
            ],
            'capacidade' => 'required|integer',
            'km_atual' => 'required|integer|min:0',
            'km_revisao' => 'required|integer|min:0',
            'obs_veiculo' => 'nullable|string', 
            'status_veiculo' => [ 
                'required',
                 Rule::in(['disponível', 'reservado', 'em uso', 'manutenção']),
            ]
        ]);

        $veiculo->update($validatedData);

        return redirect()->route('veiculos.index')->with('success', 'Veículo editado com sucesso');
    }

    public function destroy($id)
    {
        $veiculo = Veiculo::findOrFail($id);

        
        if ($veiculo->qr_code && file_exists(public_path('qrcodes/' . $veiculo->qr_code))) {
            @unlink(public_path('qrcodes/' . $veiculo->qr_code));
        }

        $veiculo->delete();

        return redirect()->route('veiculos.index')->with('success', 'Veículo deletado com sucesso');
    }

    public function mudarStatus(Request $request, $id)
    {
        try {
            $veiculo = Veiculo::findOrFail($id);

            $request->validate([
                'status_veiculo' => [
                    'required',
                    Rule::in(['disponível', 'manutenção']), 
                ],
            ]);

            $veiculo->update([
                'status_veiculo' => $request->status_veiculo
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status atualizado para ' . $request->status_veiculo . ' com sucesso!' 
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Status inválido fornecido.',
                'errors' => $e->errors(), 
            ], 422); 

        } catch (\Exception $e) {
            Log::error("Erro ao mudar status do veículo ID {$id}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar status: ' . $e->getMessage()
            ], 500); 
        }
    }

    public function solicitarCarro(Request $request, $id)
    {
        $veiculo = Veiculo::findOrFail($id);
        return view('solicitar.create', compact('veiculo'));
    }

    public function solicitarIndex(Request $request)
    {
        $query = Veiculo::query()->with(['marca', 'modelo']); 

        $query->where('status_veiculo', 'disponível'); 

        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('placa', 'like', $searchTerm)
                  ->orWhere('chassi', 'like', $searchTerm)
                  ->orWhereHas('marca', function ($subQuery) use ($searchTerm) {
                      $subQuery->where('marca', 'like', $searchTerm);
                  })
                  ->orWhereHas('modelo', function ($subQuery) use ($searchTerm) {
                      $subQuery->where('modelo', 'like', $searchTerm);
                  });
            });
        }

        $veiculos = $query->orderBy('id', 'desc')->paginate(15); 
        return view('solicitar.index', compact('veiculos'));
    }
}