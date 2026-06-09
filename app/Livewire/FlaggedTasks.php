<?php

namespace App\Livewire;

use App\Models\Task;
use Livewire\Component;

class FlaggedTasks extends Component
{
    public string $tab = 'needs_discussion';

    public function render()
    {
        $user   = auth()->user();
        $typeId = $user->last_project_type_id;

        $tasks = Task::with(['status', 'project', 'assignee:id,name,avatar_color'])
            ->whereHas('project', fn($q) => $typeId ? $q->where('type_id', $typeId) : $q)
            ->where($this->tab, true)
            ->orderByDesc('updated_at')
            ->get();

        return view('livewire.flagged-tasks', [
            'tasks'      => $tasks,
            'activeType' => $user->lastProjectType,
        ])->layout('layouts.app', ['title' => 'Flagged Tasks']);
    }
}
