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
        
        // Admins see all notifications (sent by them or received)
        if ($user->isAdmin()) {
            $query = Notification::with(['sender.person', 'receiver.person'])
                ->where(function($q) use ($user) {
                    $q->where('sender_id', $user->id)
                      ->orWhere('receiver_id', $user->id)
                      ->orWhereNull('receiver_id'); // Broadcasts
                });
        } 
        // Vigilantes and Residents see notifications they sent or received
        else {
            $query = Notification::with(['sender.person', 'receiver.person'])
                ->where(function($q) use ($user) {
                    $q->where('sender_id', $user->id)
                      ->orWhere('receiver_id', $user->id);
                });
        }

        return $query->orderBy('created_at', 'desc')->paginate($request->input('per_page', 10));
    }

    public function store(Request $request)
    {
        \Log::info('Notification store request received', ['data_size' => strlen($request->getContent())]);
        $user = Auth::user();
        $validated = $request->validate([
            'receiver_id' => 'nullable|exists:users,id',
            'title' => 'required|string',
            'message' => 'required|string',
            'type' => 'required|in:info,alert',
            'attachment' => 'nullable|string', // Base64 encoded file
            'attachment_name' => 'nullable|string', // Original filename
        ]);

        if ($user->isResident()) {
            // Residents can only send to admins. If receiver_id is null, it's for all admins? 
            // For now, let's find the first admin if receiver_id is null.
            if (!$validated['receiver_id']) {
                $admin = User::whereHas('role', function($q) { $q->where('name', 'admin'); })->first();
                $validated['receiver_id'] = $admin ? $admin->id : null;
            }
        }

        // Handle base64 attachment or chunked file path
        $attachmentData = null;
        if (!empty($validated['attachment'])) {
            $attachmentData = json_encode([
                'data' => $validated['attachment'],
                'name' => $validated['attachment_name'] ?? 'attachment',
            ]);
        } elseif ($request->has('attachment_path')) {
            $attachmentData = json_encode([
                'path' => $request->input('attachment_path'),
                'name' => $validated['attachment_name'] ?? 'attachment',
            ]);
        }

        $notification = Notification::create([
            'sender_id' => $user->id,
            'receiver_id' => $validated['receiver_id'],
            'title' => $validated['title'],
            'message' => $validated['message'],
            'type' => $validated['type'],
            'attachment' => $attachmentData,
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
