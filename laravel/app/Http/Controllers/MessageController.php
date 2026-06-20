<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function store(Request $request, Channel $channel)
    {
        $this->authorize('view', $channel->workspace);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $channel->messages()->create([
            'user_id' => Auth::id(),
            'body'    => $data['body'],
        ]);

        return redirect()->route('channels.show', $channel);
    }

    public function update(Request $request, Message $message)
    {
        abort_unless($message->user_id === Auth::id(), 403);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $message->update([
            'body'      => $data['body'],
            'edited_at' => now(), 
        ]);

        return redirect()->route('channels.show', $message->channel);
    }

    public function destroy(Message $message)
    {
        $isAuthor = $message->user_id === Auth::id();
        $isOwner  = $message->channel->workspace->owner_id === Auth::id();

        abort_unless($isAuthor || $isOwner, 403);

        $channel = $message->channel;

        $message->delete();

        return redirect()->route('channels.show', $channel);
    }
}