<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkspaceController extends Controller
{
    public function index()
    {
        $workspaces = Auth::user()->workspaces
            ->merge(Auth::user()->ownedWorkspaces)
            ->unique('id');

        return view('workspaces.index', compact('workspaces'));
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
}