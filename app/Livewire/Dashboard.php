<?php

namespace App\Livewire;

use App\Models\Project;
use App\Models\ProjectType;
use Livewire\Component;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = 'active';
    public ?int $activeProjectTypeId = null;

    public function mount(): void
    {
        $this->activeProjectTypeId = auth()->user()->last_project_type_id;
    }

    public function setProjectType(?int $id): void
    {
        $this->activeProjectTypeId = $id;
        auth()->user()->update(['last_project_type_id' => $id]);
        $this->resetPage();
    }

    public function render()
    {
        $projects = Project::with(['type', 'owner'])
            ->withCount(['tasks', 'tasks as open_tasks_count' => fn($q) => $q->whereHas('status', fn($q2) => $q2->where('is_done', false))])
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->activeProjectTypeId, fn($q) => $q->where('type_id', $this->activeProjectTypeId))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->orderBy('updated_at', 'desc')
            ->paginate(12);

        $types = ProjectType::all();
        $activeType = $this->activeProjectTypeId
            ? $types->firstWhere('id', $this->activeProjectTypeId)
            : null;

        return view('livewire.dashboard', compact('projects', 'types', 'activeType'))
            ->layout('layouts.app', ['title' => 'Dashboard']);
    }
}
