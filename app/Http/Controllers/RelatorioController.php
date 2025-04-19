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

        // Admin vê tudo, usuário vê só suas solicitações
        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        // Filtro por ID de solicitação se enviado
        if ($request->has('solicitacao_id')) {
            $query->where('id', $request->solicitacao_id);
        }

        $registros = $query->with(['veiculo', 'user'])->orderBy('created_at', 'desc')->get();

        return response()->json($registros);
    }

    public function downloadPdf(Request $request)
    {
        $user = Auth::user();

        $query = Solicitar::query();

        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        if ($request->has('solicitacao_id')) {
            $query->where('id', $request->solicitacao_id);
        }

        $registros = $query->with(['veiculo', 'user'])->get();

        $pdf = Pdf::loadView('pdf.relatorio_veiculos', compact('registros'));
        return $pdf->download('relatorio_veiculos.pdf');
    }
}

