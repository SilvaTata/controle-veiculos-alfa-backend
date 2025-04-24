<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\HistSolicitar;  
use App\Models\Solicitar;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Illuminate\Support\Optional; 
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill; 
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class WebSolicitarController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = Solicitar::with(['veiculo.marca', 'veiculo.modelo', 'user'])
                          ->whereIn('situacao', ['pendente', 'aceita']);

        if ($user->cargo_id != 1) {
            $query->where('user_id', $user->id);
        }

        $solicitars = $query->orderBy('prev_data_inicio')->orderBy('prev_hora_inicio')->get();

        return view('solicitar.show', compact('solicitars'));
    }

    public function solicitacoesRecusadas()
    {
        $user = Auth::user();
        $query = Solicitar::with(['veiculo.marca', 'veiculo.modelo', 'user', 'adm'])
                          ->where('situacao', 'recusada');

        if ($user->cargo_id != 1) {
            $query->where('user_id', $user->id);
        }

        $solicitars = $query->orderByDesc('data_recusa')->orderByDesc('hora_recusa')->get();

        return view('solicitar.solrecusada', compact('solicitars'));
    }

    public function finalizadas()
    {
        $user = Auth::user();
        $query = Solicitar::with(['veiculo.marca', 'veiculo.modelo', 'user', 'adm', 'historico', 'hist_veiculo'])
                          ->where('situacao', 'concluída');

        if ($user->cargo_id != 1) {
            $query->where('user_id', $user->id);
        }

        $solicitars = $query->orderByDesc(
                             HistSolicitar::select('data_final')
                                 ->whereColumn('hist_solicitars.solicitacao_id', 'solicitars.id')
                                 ->limit(1) 
                          )
                          ->orderByDesc('created_at') 
                          ->get();


        return view('solicitar.finalizadas', compact('solicitars'));
    }

    public function create($veiculo_id)
    {
        $veiculo = Veiculo::with(['marca', 'modelo'])->findOrFail($veiculo_id);
        return view('solicitar.create', compact('veiculo'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'veiculo_id' => 'required|exists:veiculos,id',
            'prev_data_inicio' => 'required|date|after_or_equal:today',
            'prev_hora_inicio' => 'required|date_format:H:i',
            'prev_data_final' => 'required|date|after_or_equal:prev_data_inicio',
            'prev_hora_final' => ['required', 'date_format:H:i', function ($attribute, $value, $fail) use ($request) {
                if ($request->prev_data_inicio == $request->prev_data_final && $value <= $request->prev_hora_inicio) {
                    $fail('A hora de devolução deve ser posterior à hora de retirada no mesmo dia.');
                }
            }],
            'motivo' => 'required|string|max:500',
        ]);

        $solicitar = Solicitar::create([
            'user_id' => Auth::id(),
            'veiculo_id' => $validatedData['veiculo_id'],
            'prev_data_inicio' => $validatedData['prev_data_inicio'],
            'prev_hora_inicio' => $validatedData['prev_hora_inicio'],
            'prev_data_final' => $validatedData['prev_data_final'],
            'prev_hora_final' => $validatedData['prev_hora_final'],
            'motivo' => $validatedData['motivo'],
        ]);

        return redirect()->route('solicitar.index')->with('success', 'Sua solicitação foi enviada com sucesso!');
    }

    public function ver($id) 
{
    $solicitacao = Solicitar::findOrFail($id);
    
    $solicitacao->load(['user', 'veiculo.marca', 'veiculo.modelo', 'adm', 'historico', 'hist_veiculo']);

    $veiculoFinal = $solicitacao->veiculo; 

    return view('solicitar.ver', [
        'solicitacao' => $solicitacao, 
        'veiculo' => $veiculoFinal     
    ]);
}

    public function verRecusada($id)
    {
        $solicitar = Solicitar::findOrFail($id);
        if ($solicitar->situacao !== 'recusada') {
             abort(404, 'Solicitação não encontrada ou não está recusada.');
        }
        $solicitar->load(['user', 'veiculo.marca', 'veiculo.modelo', 'adm']);
        $veiculo = $solicitar->veiculo;

        return view('solicitar.verrecusada', compact('solicitar', 'veiculo'));
    }

    // public function start(Solicitar $solicitar)
    // {
    //     if ($solicitar->situacao !== 'aceita' || $solicitar->historico)->data_inicio) {
    //          return redirect()->route('solicitar.index')->with('error', 'Esta solicitação não pode ser iniciada.');
    //     }

    //     $solicitar->load('veiculo');
    //     $veiculo = $solicitar->veiculo;
    //     $kmAtualVeiculo = $veiculo->km_atual;

    //     return view('solicitar.start', compact('solicitar', 'veiculo', 'kmAtualVeiculo'));
    // }

    // public function prosseguir(Request $request, Solicitar $solicitar)
    // {
    //     if ($solicitar->situacao !== 'aceita' || optional($solicitar->historico)->data_inicio) {
    //         return redirect()->route('solicitar.index')->with('error', 'Esta solicitação não pode ser iniciada ou já foi iniciada.');
    //     }

    //     $solicitar->load('veiculo');
    //     $veiculo = $solicitar->veiculo;

    //     $request->validate([
    //         'placa_confirmar' => 'required|string',
    //         'km_inicio' => 'required|integer|min:0',
    //     ]);

    //     if (strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $request->input('placa_confirmar'))) !== strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $veiculo->placa))) {
    //         return redirect()->back()->withInput()->with('error', 'A placa informada não corresponde à placa do veículo.');
    //     }

    //     if ($request->km_inicio < $veiculo->km_atual) {
    //          return redirect()->back()->withInput()->with('error', 'A quilometragem inicial informada (' . $request->km_inicio . ') é menor que a quilometragem atual registrada para o veículo (' . $veiculo->km_atual . '). Verifique o valor ou atualize o cadastro do veículo.');
    //     }

    //     DB::beginTransaction();
    //     try {
    //         $histVeiculo = HistVeiculo::create([
    //             'solicitacao_id' => $solicitar->id,
    //             'veiculo_id' => $veiculo->id,
    //             'km_inicio' => $request->km_inicio,
    //             'km_final' => $request->km_inicio, 
    //         ]);

    //          $histSolicitar = HistSolicitar::updateOrCreate(
    //             ['solicitacao_id' => $solicitar->id],
    //             [
    //                 'data_inicio' => Carbon::now()->format('Y-m-d'),
    //                 'hora_inicio' => Carbon::now()->format('H:i:s'),
    //             ]
    //         );

    //         $veiculo->status_veiculo = 'em uso';
    //         $veiculo->km_atual = $request->km_inicio;
    //         $veiculo->save();


    //          DB::commit();
    //          return redirect()->route('solicitar.end', ['solicitar' => $solicitar->id])->with('success', 'Viagem iniciada com sucesso! Registre a finalização ao retornar.');

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return redirect()->back()->withInput()->with('error', 'Erro ao iniciar a viagem. Tente novamente.');
    //     }
    // }

    // public function end(Solicitar $solicitar)
    // {
    //     $solicitar->load(['veiculo', 'historico', 'hist_veiculo']);

    //     // Usando as relações com os nomes corretos para verificar
    //     if ($solicitar->situacao !== 'aceita' || !optional($solicitar->historico)->data_inicio || !optional($solicitar->hist_veiculo)->km_inicio) {
    //         return redirect()->route('solicitar.index')->with('error', 'Esta solicitação não foi iniciada ou já foi finalizada.');
    //     }

    //     $veiculo = $solicitar->veiculo;
    //     // Acessando km_inicio através da relação 'hist_veiculo'
    //     $kmInicialRegistrado = $solicitar->hist_veiculo->km_inicio;

    //     return view('solicitar.end', compact('solicitar', 'veiculo', 'kmInicialRegistrado'));
    // }

    /**
     * Processa a finalização da viagem.
     */
    // public function finalizar(Request $request, Solicitar $solicitar)
    // {
    //     // Usando 'historico' e 'hist_veiculo' no load()
    //     $solicitar->load(['veiculo', 'historico', 'hist_veiculo']);

    //     // Acessando as relações com os nomes corretos
    //     $histSolicitar = $solicitar->historico;
    //     $histVeiculo = $solicitar->hist_veiculo;
    //     $veiculo = $solicitar->veiculo;

    //     if ($solicitar->situacao !== 'aceita' || !$histSolicitar || !$histVeiculo || !$histSolicitar->data_inicio) {
    //          return redirect()->route('solicitar.index')->with('error', 'Solicitação não pode ser finalizada neste estado.');
    //     }

    //     $request->validate([
    //         'placa_confirmar2' => 'required|string',
    //         'km_final' => 'required|integer|min:' . $histVeiculo->km_inicio,
    //         'obs_users' => 'nullable|string|max:1000',
    //     ]);

    //     if (strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $request->input('placa_confirmar2'))) !== strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $veiculo->placa))) {
    //         return redirect()->back()->withInput()->with('error', 'A placa informada não corresponde à placa do veículo.');
    //     }

    //     DB::beginTransaction();
    //     try {
    //         $histVeiculo->km_final = $request->km_final;
    //         $histVeiculo->save();

    //         $histSolicitar->hora_final = Carbon::now()->format('H:i:s');
    //         $histSolicitar->data_final = Carbon::now()->format('Y-m-d');
    //         $histSolicitar->obs_users = $request->obs_users;
    //         $histSolicitar->save();

    //         // Atualiza Solicitar
    //         $solicitar->situacao = 'concluída';
    //         $solicitar->save();

    //         // Atualiza Veiculo
    //         $veiculo->km_atual = $request->km_final;
    //         $veiculo->status_veiculo = 'disponível';
    //         $veiculo->save();

    //         DB::commit();
    //         return redirect()->route('solicitar.index')->with('success', 'Solicitação finalizada com sucesso!');

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return redirect()->back()->withInput()->with('error', 'Erro ao finalizar a viagem. Tente novamente.');
    //     }
    // }

    public function aceitar($id)
    {
        $user = Auth::user();
        $solicitar = Solicitar::find($id);
        if ($solicitar->situacao !== 'pendente') {
             return redirect()->route('solicitar.index')->with('error', 'Solicitação não pode ser aceita.');
        }


         DB::beginTransaction();
         try {
            $solicitar->situacao = 'aceita';
            $solicitar->adm_id = $user->id; 
            $solicitar->save();

            HistSolicitar::updateOrCreate(
                ['solicitacao_id' => $solicitar->id],
                [
                    'adm_id' => $user->id, 
                    'data_aceito' => Carbon::now()->format('Y-m-d'),
                    'hora_aceito' => Carbon::now()->format('H:i:s'),
                ]
            );

            DB::commit();
            return redirect()->route('solicitar.index')->with('success', 'Solicitação aceita com sucesso.');

         } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('solicitar.index')->with('error', 'Erro ao aceitar a solicitação.');
         }
    }

    public function recusar($id)
    {
        $solicitar = Solicitar::find($id);
        if ($solicitar->situacao !== 'pendente') {
             return redirect()->route('solicitar.show')->with('error', 'Solicitação não pode ser recusada.');
        }

        return view('solicitar.recusado', compact('solicitar'));
    }

    public function motivoRecusado(Request $request, $id)
    {
        $user = Auth::user();
        $solicitar = Solicitar::find($id);
        if ($solicitar->situacao !== 'pendente') {
             return redirect()->route('solicitar.show', ['id' => $solicitar->id])->with('error', 'Solicitação não pode ser recusada ou já foi tratada.');
        }
        $request->validate([
            'motivo_recusa' => 'required|string|max:500',
        ]);
        
        DB::beginTransaction();
        try {
            $solicitar->situacao = 'recusada';
            $solicitar->motivo_recusa = $request->motivo_recusa;
            $solicitar->adm_id = $user->id;
            $solicitar->data_recusa = Carbon::now()->format('Y-m-d');
            $solicitar->hora_recusa = Carbon::now()->format('H:i:s');
            $solicitar->save();
            
            DB::commit();
            return redirect()->route('solicitar.show', ['id' => $solicitar->id])->with('success', 'Solicitação recusada com sucesso.');

        } catch (\Exception $e) {
             DB::rollBack();
            return redirect()->route('solicitar.show')->with('error', 'Erro ao recusar a solicitação.');
        }
    }

    public function gerarPDF($id)
    {
        $solicitar = Solicitar::findOrFail($id);

        $solicitar->load(['user', 'veiculo.marca', 'adm', 'historico.adm','veiculo.modelo', 'historico', 'hist_veiculo']);

        $userAdm = $solicitar->historico->adm->name;

        $html = view('solicitar.pdf', compact('solicitar', 'userAdm'))->render();

        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'solicitacao_' . $solicitar->id . '_' . $solicitar->user->name . '.pdf';
        $filename = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $filename);

        return response($dompdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
    }

    public function exportarTodasExcel()
    {
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $headerRow = 1;
        $dataRowStart = 2;

        $headers = [
            'A' => 'O.S', 'B' => 'Colaborador', 'C' => 'ID Colab.', 'D' => 'Email Colab.',
            'E' => 'Veículo', 'F' => 'Placa', 'G' => 'Data Prev. Retirada', 'H' => 'Hora Prev. Retirada',
            'I' => 'Data Prev. Devolução', 'J' => 'Hora Prev. Devolução', 'K' => 'Motivo Solicitação',
            'L' => 'Responsável Aprovação', 'M' => 'Data Aceite', 'N' => 'Hora Aceite',
            'O' => 'Data Início Real', 'P' => 'Hora Início Real', 'Q' => 'Data Fim Real',
            'R' => 'Hora Fim Real', 'S' => 'KM Inicial', 'T' => 'KM Final', 'U' => 'KMs Rodados',
            'V' => 'Observações Finais',
        ];

        foreach ($headers as $col => $title) {
            $sheet->setCellValue($col . $headerRow, $title);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => '4CAF50']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $sheet->getStyle('A' . $headerRow . ':' . array_key_last($headers) . $headerRow)->applyFromArray($headerStyle);
        $sheet->getRowDimension($headerRow)->setRowHeight(20);

        // Usando 'historico', 'hist_veiculo', 'adm' no with()
        $solicitacoes = Solicitar::with([
            'user', 'veiculo.marca', 'veiculo.modelo', 'adm', 'historico', 'hist_veiculo'
        ])->where('situacao', 'concluída')->orderBy('id')->get();

        
        $row = $dataRowStart;
        foreach ($solicitacoes as $sol) {
            $userAdm = $sol->historico->adm->name;
            // Usando ) e os nomes corretos das relações
            $histS = $sol->historico;
            $histV = $sol->hist_veiculo;
            $adm = $userAdm; // Admin da tabela solicitars
            $veiculo = $sol->veiculo;
            $user = $sol->user;
            $marca = $veiculo->marca->marca;
            $modelo = $veiculo->modelo->modelo;

            $sheet->setCellValue('A' . $row, $sol->id);
            $sheet->setCellValue('B' . $row, $user->name);
            $sheet->setCellValue('C' . $row, $user->id);
            $sheet->setCellValue('D' . $row, $user->email);
            $sheet->setCellValue('E' . $row, $marca . ' ' . $modelo);
            $sheet->setCellValue('F' . $row, $veiculo->placa);
            $sheet->setCellValue('G' . $row, Carbon::parse($sol->data_inicio)->format('d/m/Y'));
            $sheet->setCellValue('H' . $row, Carbon::parse($sol->hora_inicio)->format('H:i'));
            $sheet->setCellValue('I' . $row, Carbon::parse($sol->data_final)->format('d/m/Y'));
            $sheet->setCellValue('J' . $row, Carbon::parse($sol->hora_final)->format('H:i'));
            $sheet->setCellValue('K' . $row, $sol->motivo);
            $sheet->setCellValue('L' . $row, $adm); // Nome do admin que aceitou/recusou
            $sheet->setCellValue('M' . $row, $histS->data_aceito ? Carbon::parse($histS->data_aceito)->format('d/m/Y') : '');
            $sheet->setCellValue('N' . $row, $histS->hora_aceito ? Carbon::parse($histS->hora_aceito)->format('H:i') : '');
            $sheet->setCellValue('O' . $row, $histS->data_inicio ? Carbon::parse($histS->data_inicio)->format('d/m/Y') : '');
            $sheet->setCellValue('P' . $row, $histS->hora_inicio ? Carbon::parse($histS->hora_inicio)->format('H:i') : '');
            $sheet->setCellValue('Q' . $row, $histS->data_final ? Carbon::parse($histS->data_final)->format('d/m/Y') : '');
            $sheet->setCellValue('R' . $row, $histS->hora_final ? Carbon::parse($histS->hora_final)->format('H:i') : '');
            $sheet->setCellValue('S' . $row, $histV->km_inicio);
            $sheet->setCellValue('T' . $row, $histV->km_final);
            $sheet->setCellValue('U' . $row, $histV->km_gasto); // Campo calculado
            $sheet->setCellValue('V' . $row, $histS->obs_users);
            $row++;
        }

        $lastCol = array_key_last($headers);
        $lastRow = $row - 1;

        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        if ($lastRow >= $dataRowStart) {
            $sheet->getStyle("A{$dataRowStart}:{$lastCol}{$lastRow}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFC0C0C0']]],
            ]);
            $sheet->getStyle("K{$dataRowStart}:K{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("V{$dataRowStart}:V{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("S{$dataRowStart}:U{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'relatorio_solicitacoes_concluidas_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}