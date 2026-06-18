<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Workspace;
use Illuminate\Http\Request;

class ChannelController extends Controller
{
    public function store(Request $request, Workspace $workspace)
    {
        $this->authorize('update', $workspace);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $workspace->channels()->create($data);

        return redirect()->route('workspaces.show', $workspace);
    }

    public function destroy(Channel $channel)
    {
        $this->authorize('update', $channel->workspace);

        $workspace = $channel->workspace;

        $channel->delete();

        return redirect()->route('workspaces.show', $workspace);
    }
}