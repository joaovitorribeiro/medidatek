<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::query()
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get([
                'id',
                'name',
                'url',
                'tag',
                'note',
                'sort_order',
                'is_published',
                'created_at',
            ]);

        return Inertia::render('Admin/Projects/Index', [
            'projects' => $projects,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Projects/Form', [
            'project' => null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'url' => ['required', 'url', 'max:500'],
            'tag' => ['nullable', 'string', 'max:80'],
            'note' => ['nullable', 'string', 'max:280'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_published'] = (bool) ($data['is_published'] ?? true);

        Project::create($data);

        return redirect()->route('admin.projects.index');
    }

    public function edit(Project $project)
    {
        return Inertia::render('Admin/Projects/Form', [
            'project' => $project->only(['id', 'name', 'url', 'tag', 'note', 'sort_order', 'is_published']),
        ]);
    }

    public function update(Request $request, Project $project)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'url' => ['required', 'url', 'max:500'],
            'tag' => ['nullable', 'string', 'max:80'],
            'note' => ['nullable', 'string', 'max:280'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_published'] = (bool) ($data['is_published'] ?? false);

        $project->update($data);

        return redirect()->route('admin.projects.index');
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return redirect()->route('admin.projects.index');
    }
}
