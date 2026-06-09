<?php

namespace App\Livewire\Admin;

use App\Models\ProjectType;
use Livewire\Component;

class ProjectTypeManager extends Component
{
    public string $name = '';
    public string $color = '#6366f1';
    public string $icon = 'folder';
    public ?int $editingId = null;

    public function save(): void
    {
        $this->validate([
            'name'  => 'required|string|max:50',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon'  => 'required|string|max:30',
        ]);

        if ($this->editingId) {
            ProjectType::findOrFail($this->editingId)->update([
                'name' => $this->name, 'color' => $this->color, 'icon' => $this->icon,
            ]);
        } else {
            ProjectType::create([
                'name' => $this->name, 'color' => $this->color,
                'icon' => $this->icon, 'created_by' => auth()->id(),
            ]);
        }

        $this->reset(['name', 'color', 'icon', 'editingId']);
    }

    public function edit(int $id): void
    {
        $type = ProjectType::findOrFail($id);
        $this->editingId = $type->id;
        $this->name = $type->name;
        $this->color = $type->color;
        $this->icon = $type->icon;
    }

    public function delete(int $id): void
    {
        ProjectType::findOrFail($id)->delete();
    }

    public function render()
    {
        return view('livewire.admin.project-type-manager', [
            'types' => ProjectType::withCount('projects')->get(),
        ])->layout('layouts.app', ['title' => 'Project Types']);
    }
}
