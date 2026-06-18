<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkspaceController extends Controller
{
    public function index(Request $request)
    {
        $query = trim((string) $request->input('q', ''));

        $results = $query === ''
            ? collect()
            : Workspace::where('visibility', 'public')
                ->where('name', 'like', '%'.$query.'%')
                ->orderBy('name')
                ->limit(20)
                ->get();

        $myIds = Auth::user()->ownedWorkspaces()->pluck('workspaces.id')
            ->merge(Auth::user()->workspaces()->pluck('workspaces.id'))
            ->unique();

        return view('workspaces.index', compact('query', 'results', 'myIds'));
    }

    public function create()
    {
        $this->authorize('create', Workspace::class);

        return view('workspaces.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Workspace::class);

        $data = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'visibility' => ['required', 'in:public,private'],
        ]);

        $workspace = Auth::user()->ownedWorkspaces()->create($data);
        $workspace->members()->attach(Auth::id());

        return redirect()->route('workspaces.show', $workspace);
    }

    public function show(Workspace $workspace)
    {
        $this->authorize('view', $workspace);

        $workspace->load('channels');

        return view('workspaces.show', compact('workspace'));
    }

    public function edit(Workspace $workspace)
    {
        $this->authorize('update', $workspace);

        return view('workspaces.edit', compact('workspace'));
    }

    public function update(Request $request, Workspace $workspace)
    {
        $this->authorize('update', $workspace);

        $data = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'visibility' => ['required', 'in:public,private'],
        ]);

        $workspace->update($data);

        return redirect()->route('workspaces.show', $workspace);
    }

    public function destroy(Workspace $workspace)
    {
        $this->authorize('delete', $workspace);

        $workspace->delete();

        return redirect()->route('workspaces.index');
    }

    public function join(Workspace $workspace)
    {
        abort_unless($workspace->visibility === 'public', 403);

        $workspace->members()->syncWithoutDetaching([Auth::id()]);

        return redirect()->route('workspaces.show', $workspace);
    }
}