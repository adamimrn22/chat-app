<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Message;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index($channelId)
    {
        $channel = Channel::findOrFail($channelId);

        // Verify the user is part of this channel
        if (!$channel->users->contains(Auth::user())) {
            abort(403, 'Unauthorized access to this channel');
        }

        $messages = Message::where('channel_id', $channel->id)
            ->with('user')
            ->latest()
            ->get();

        return view('chat', compact('messages', 'channel'));
    }

    public function sendMessage(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:500',
            'channel_id' => 'required|exists:channels,id'
        ]);

        $message = Message::create([
            'user_id' => Auth::id(),
            'channel_id' => $validated['channel_id'],
            'message' => $validated['message']
        ]);

        $message = $message->load(['user', 'channel']);

        // Broadcast to the specific channel
        broadcast(new MessageSent($message));

        return response()->json($message);
    }
}
