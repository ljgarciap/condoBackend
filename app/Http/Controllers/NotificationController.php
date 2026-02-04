<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Residents only see notifications for them
        // Admins see all sent by them or for them
        if ($user->isAdmin()) {
            $query = Notification::with(['sender.person', 'receiver.person']);
        } else {
            $query = Notification::where('receiver_id', $user->id)
                ->orWhereNull('receiver_id') // Broadcasts
                ->with(['sender.person']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($request->input('per_page', 10));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'receiver_id' => 'nullable|exists:users,id',
            'title' => 'required|string',
            'message' => 'required|string',
            'type' => 'required|in:info,alert',
        ]);

        if ($user->isResident()) {
            // Residents can only send to admins. If receiver_id is null, it's for all admins? 
            // For now, let's find the first admin if receiver_id is null.
            if (!$validated['receiver_id']) {
                $admin = User::whereHas('role', function($q) { $q->where('name', 'admin'); })->first();
                $validated['receiver_id'] = $admin ? $admin->id : null;
            }
        }

        $notification = Notification::create([
            'sender_id' => $user->id,
            'receiver_id' => $validated['receiver_id'],
            'title' => $validated['title'],
            'message' => $validated['message'],
            'type' => $validated['type'],
        ]);

        return response()->json($notification, 201);
    }

    public function markAsRead(Notification $notification)
    {
        if ($notification->receiver_id === Auth::id() || Auth::user()->isAdmin()) {
            $notification->update(['read_at' => now()]);
            return response()->json($notification);
        }

        return response()->json(['message' => 'Unauthorized'], 403);
    }

    public function acceptPolicies()
    {
        $user = Auth::user();
        $user->update(['policies_accepted_at' => now()]);
        
        return response()->json(['message' => 'Políticas aceptadas correctamente']);
    }
}
