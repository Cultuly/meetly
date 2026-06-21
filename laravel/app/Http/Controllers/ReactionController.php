<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Reaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\Rule;

class ReactionController extends Controller
{
    private const EMOJI = [
        'like'  => '👍',
        'love'  => '❤️',
        'laugh' => '😂',
        'party' => '🎉',
    ];

    public function toggle(Request $request, Message $message)
    {
        $this->authorize('view', $message->channel->workspace);

        $data = $request->validate([
            'emoji' => ['required', Rule::in(array_keys(self::EMOJI))],
        ]);

        $existing = $message->reactions()
            ->where('user_id', Auth::id())
            ->where('emoji', $data['emoji'])
            ->first();

        if ($existing) {
            $existing->delete();
            $action = 'removed';
        } else {
            $message->reactions()->create([
                'user_id' => Auth::id(),
                'emoji'   => $data['emoji'],
            ]);
            $action = 'added';
        }

        Redis::publish("channel.{$message->channel_id}", json_encode([
            'type'       => 'reaction.toggled', 
            'channel_id' => $message->channel_id,
            'message_id' => $message->id,      
            'emoji'      => $data['emoji'],    
            'action'     => $action,         
            'user_id'    => Auth::id(),      
            'count'      => $message->reactions()->where('emoji', $data['emoji'])->count(),
        ]));

        return back();
    }
}