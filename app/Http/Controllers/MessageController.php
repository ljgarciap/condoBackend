<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        
        // Return messages where user is sender or receiver
        // Or return inbox/outbox separately. For simplicity, let's return conversations or just inbox.
        // Let's implement inbox.
        return Message::with('sender')
            ->where('receiver_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function sent(Request $request)
    {
        $userId = $request->user()->id;
        return Message::with('receiver')
            ->where('sender_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'content' => 'required|string',
        ]);

        $message = Message::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $validated['receiver_id'],
            'content' => $validated['content'],
        ]);

        return response()->json($message, 201);
    }

    public function show(Message $message)
    {
        $this->authorizeAccess($message);
        return $message->load(['sender', 'receiver']);
    }

    public function markAsRead(Message $message)
    {
        $this->authorizeAccess($message);
        
        if ($message->receiver_id === Auth::id()) {
            $message->update(['read_at' => now()]);
        }

        return response()->json($message);
    }

    private function authorizeAccess(Message $message)
    {
        $userId = Auth::id();
        if ($message->sender_id !== $userId && $message->receiver_id !== $userId) {
            abort(403, 'Unauthorized access to this message.');
        }
    }
}
