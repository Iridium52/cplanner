<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use App\Models\ProjectType;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ProjectCreate extends Component
{
    public string $name = '';
    public string $key = '';
    public string $description = '';
    public string $color = '#6366f1';
    public string $type_id = '';

    protected function rules(): array
    {
        return [
            'name'        => 'required|string|max:100',
            'key'         => 'required|string|max:10|alpha_num|uppercase|unique:projects,key',
            'description' => 'nullable|string|max:1000',
            'color'       => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'type_id'     => 'nullable|exists:project_types,id',
        ];
    }

    public function updatedName(string $value): void
    {
        if (empty($this->key)) {
            $this->key = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', substr($value, 0, 6)));
        }
    }

    public function save(): void
    {
        $this->validate();

        $project = Project::create([
            'name'        => $this->name,
            'key'         => strtoupper($this->key),
            'description' => $this->description,
            'color'       => $this->color,
            'type_id'     => $this->type_id ?: null,
            'owner_id'    => auth()->id(),
        ]);

        $project->seedDefaultStatuses();

        $this->redirectRoute('projects.show', $project, navigate: true);
    }

    public function render()
    {
        return view('livewire.projects.project-create', [
            'types' => ProjectType::all(),
        ])->layout('layouts.app', ['title' => 'New Project']);
    }
}
