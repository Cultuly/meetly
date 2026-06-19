<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Reaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        } else {
            $message->reactions()->create([
                'user_id' => Auth::id(),
                'emoji'   => $data['emoji'],  
            ]);
        }

        return back();
    }
}