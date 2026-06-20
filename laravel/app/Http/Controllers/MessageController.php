<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

class MessageController extends Controller
{
    public function store(Request $request, Channel $channel)
    {
        $this->authorize('view', $channel->workspace);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $message = $channel->messages()->create([
            'user_id' => Auth::id(),
            'body'    => $data['body'],
        ]);
        $message->load('user');

        Redis::publish("channel.{$channel->id}", json_encode([
            'type'       => 'message.created',
            'channel_id' => $channel->id,
            'message'    => [
                'id'         => $message->id,
                'body'       => $message->body,
                'user'       => [
                    'id'   => $message->user->id,
                    'name' => $message->user->name,
                ],
                'created_at' => $message->created_at->toIso8601String(),
            ],
        ]));

        return redirect()->route('channels.show', $channel);
    }

    public function update(Request $request, Message $message)
    {
        abort_unless($message->user_id === Auth::id(), 403);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $message->body      = $data['body'];
        $message->edited_at = now();
        $message->save();

        Redis::publish("channel.{$message->channel_id}", json_encode([
            'type'       => 'message.updated',
            'channel_id' => $message->channel_id,
            'message'    => [
                'id'        => $message->id,
                'body'      => $message->body,
                'edited_at' => $message->edited_at->toIso8601String(),
            ],
        ]));

        return redirect()->route('channels.show', $message->channel);
    }

    public function destroy(Message $message)
    {
        $isAuthor = $message->user_id === Auth::id();
        $isOwner  = $message->channel->workspace->owner_id === Auth::id();

        abort_unless($isAuthor || $isOwner, 403);

        $channel = $message->channel;

        $messageId = $message->id;
        $channelId = $message->channel_id;

        $message->delete();

        Redis::publish("channel.{$channelId}", json_encode([
            'type'       => 'message.deleted',
            'channel_id' => $channelId,
            'message_id' => $messageId,
        ]));

        return redirect()->route('channels.show', $channel);
    }
}