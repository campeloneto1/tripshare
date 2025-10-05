<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Lista todas as notificações do usuário autenticado
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($notifications);
    }

    /**
     * Lista apenas notificações não lidas
     */
    public function unread(Request $request)
    {
        $user = $request->user();

        $notifications = $user->unreadNotifications()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($notifications);
    }

    /**
     * Marca uma notificação como lida
     */
    public function markAsRead(Request $request, string $id)
    {
        $user = $request->user();

        $notification = $user->notifications()->find($id);

        if (!$notification) {
            return response()->json(['error' => 'Notificação não encontrada'], 404);
        }

        $notification->markAsRead();

        return response()->json(['message' => 'Notificação marcada como lida']);
    }

    /**
     * Marca todas as notificações como lidas
     */
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();

        $user->unreadNotifications->markAsRead();

        return response()->json(['message' => 'Todas as notificações foram marcadas como lidas']);
    }

    /**
     * Exclui uma notificação
     */
    public function destroy(Request $request, string $id)
    {
        $user = $request->user();

        $notification = $user->notifications()->find($id);

        if (!$notification) {
            return response()->json(['error' => 'Notificação não encontrada'], 404);
        }

        $notification->delete();

        return response()->json(['message' => 'Notificação excluída']);
    }
}
