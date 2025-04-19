<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Solicitar;
use Barryvdh\DomPDF\Facade\Pdf;

class RelatorioController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Solicitar::query();

        if ($user->cargo_id === 1) {
            $registros = $query->where('situacao', 'concluída')->with(['veiculo', 'veiculo.marca', 'veiculo.modelo', 'user', 'historico', 'hist_veiculo'])->get();
            return response()->json(['registros' => $registros, 'message' => 'Registros encontrados!'], 200);
        }

        if ($user->cargo_id === 2) {
            $registros = $query->where('situacao', 'concluída')->with(['veiculo', 'veiculo.marca', 'veiculo.modelo', 'user', 'historico', 'hist_veiculo'])->get();
            return response()->json([
                'message' => $registros->isEmpty() ? 'Você não possui solicitações ativas.' : 'Suas solicitações ativas.',
                'registros' => $registros,
                // 'user' => $user,
            ], 200);
        }
    }

    // public function downloadPdf(Request $request)
    // {
    //     $user = Auth::user();

    //     $query = Solicitar::query();

    //     if ($user->role !== 'admin') {
    //         $query->where('user_id', $user->id);
    //     }

    //     if ($request->has('solicitacao_id')) {
    //         $query->where('id', $request->solicitacao_id);
    //     }

    //     $registros = $query->with(['veiculo', 'user'])->get();

    //     $pdf = Pdf::loadView('pdf.relatorio_veiculos', compact('registros'));
    //     return $pdf->download('relatorio_veiculos.pdf');
    // }
}
