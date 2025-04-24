<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;


class WebNotificationController extends Controller
{
    public function index()
    {
        // Recupera o usuário autenticado
        $user = Auth::user();

        // Recupera todas as notificações do usuário, incluindo lidas e não lidas
        $notifications = $user->notifications;

        // Retorna as notificações para exibir na página
        return view('solicitar.notificacoes', compact('notifications'));
    }

    public function count()
{
    // Recupera o usuário autenticado
    $user = Auth::user();
    
    // Conta o número de notificações não lidas
    $count = $user->unreadNotifications->count();

    // Retorna o número de notificações não lidas
    return response()->json(['count' => $count]);
}


    public function list()
    {
        // Recupera as notificações do usuário
        $user = Auth::user();
        $notifications = $user->notifications;

        return response()->json($notifications);
    }

}
