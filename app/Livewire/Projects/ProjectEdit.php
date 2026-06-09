<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use App\Models\ProjectType;
use Livewire\Component;

class ProjectEdit extends Component
{
    public Project $project;
    public string $name = '';
    public string $key = '';
    public string $description = '';
    public string $color = '';
    public string $type_id = '';
    public string $status = '';

    public function mount(Project $project): void
    {
        $this->project = $project;
        $this->name = $project->name;
        $this->key = $project->key;
        $this->description = $project->description ?? '';
        $this->color = $project->color;
        $this->type_id = (string) ($project->type_id ?? '');
        $this->status = $project->status;
    }

    public function save(): void
    {
        $this->validate([
            'name'    => 'required|string|max:100',
            'key'     => "required|string|max:10|alpha_num|uppercase|unique:projects,key,{$this->project->id}",
            'color'   => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'type_id' => 'nullable|exists:project_types,id',
            'status'  => 'required|in:active,archived',
        ]);

        $this->project->update([
            'name'        => $this->name,
            'key'         => strtoupper($this->key),
            'description' => $this->description,
            'color'       => $this->color,
            'type_id'     => $this->type_id ?: null,
            'status'      => $this->status,
        ]);

        $this->redirectRoute('projects.show', $this->project, navigate: true);
    }

    public function render()
    {
        return view('livewire.projects.project-edit', [
            'types' => ProjectType::all(),
        ])->layout('layouts.app', ['title' => 'Edit Project']);
    }
}
