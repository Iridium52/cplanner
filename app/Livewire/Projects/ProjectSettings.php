<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectStatus;
use App\Models\User;
use Livewire\Component;

class ProjectSettings extends Component
{
    public Project $project;

    // Status management
    public string $newStatusName = '';
    public string $newStatusColor = '#6366f1';
    public bool $newStatusIsDone = false;

    // Status deletion / reassignment
    public bool $showReassignModal = false;
    public ?int $deletingStatusId = null;
    public ?int $reassignToStatusId = null;

    // Category management
    public string $newCategoryName = '';
    public string $newCategoryColor = '#6366f1';

    // API token
    public string $tokenName = '';
    public array $tokenAbilities = [];
    public ?string $newToken = null;

    // Delete project
    public string $deleteConfirm = '';

    public function mount(Project $project): void
    {
        $this->project = $project->load(['statuses', 'categories']);
        $this->buildDefaultAbilities();
    }

    public function addStatus(): void
    {
        $this->validate(['newStatusName' => 'required|string|max:50']);

        $position = $this->project->statuses()->max('position') + 1;

        $this->project->statuses()->create([
            'name'     => $this->newStatusName,
            'color'    => $this->newStatusColor,
            'position' => $position,
            'is_done'  => $this->newStatusIsDone,
        ]);

        $this->reset(['newStatusName', 'newStatusColor', 'newStatusIsDone']);
        $this->project = $this->project->fresh('statuses');
    }

    public function renameStatus(int $id, string $name): void
    {
        $name = trim($name);
        if ($name === '') return;

        ProjectStatus::where('id', $id)
            ->where('project_id', $this->project->id)
            ->update(['name' => $name]);

        $this->project = $this->project->fresh('statuses');
    }

    public function reorderStatuses(array $ids): void
    {
        foreach ($ids as $position => $id) {
            ProjectStatus::where('id', $id)
                ->where('project_id', $this->project->id)
                ->update(['position' => $position]);
        }
        $this->project = $this->project->fresh('statuses');
    }

    public function deleteStatus(int $id): void
    {
        $status = ProjectStatus::findOrFail($id);
        $taskCount = $status->tasks()->count();

        if ($taskCount > 0) {
            $this->deletingStatusId = $id;
            $this->reassignToStatusId = null;
            $this->showReassignModal = true;
            return;
        }

        $status->delete();
        $this->project = $this->project->fresh('statuses');
    }

    public function confirmDeleteStatus(): void
    {
        $this->validate(['reassignToStatusId' => 'required|integer|exists:project_statuses,id']);

        $status = ProjectStatus::findOrFail($this->deletingStatusId);
        $status->tasks()->update(['status_id' => $this->reassignToStatusId]);
        $status->delete();

        $this->showReassignModal = false;
        $this->deletingStatusId = null;
        $this->reassignToStatusId = null;
        $this->project = $this->project->fresh('statuses');
    }

    public function cancelDeleteStatus(): void
    {
        $this->showReassignModal = false;
        $this->deletingStatusId = null;
        $this->reassignToStatusId = null;
    }

    public function addCategory(): void
    {
        $this->validate(['newCategoryName' => 'required|string|max:50']);

        $this->project->categories()->create([
            'name'  => $this->newCategoryName,
            'color' => $this->newCategoryColor,
        ]);

        $this->reset(['newCategoryName', 'newCategoryColor']);
        $this->project = $this->project->fresh('categories');
    }

    public function deleteCategory(int $id): void
    {
        ProjectCategory::findOrFail($id)->delete();
        $this->project = $this->project->fresh('categories');
    }

    public function createApiToken(): void
    {
        $this->validate([
            'tokenName'       => 'required|string|max:100',
            'tokenAbilities'  => 'required|array|min:1',
        ]);

        $abilities = collect($this->tokenAbilities)
            ->filter(fn($v) => $v)
            ->keys()
            ->map(fn($action) => "project:{$this->project->id}:{$action}")
            ->toArray();

        $token = auth()->user()->createToken($this->tokenName, $abilities);
        $this->newToken = $token->plainTextToken;
        $this->reset(['tokenName', 'tokenAbilities']);
        $this->buildDefaultAbilities();
    }

    public function deleteProject(): void
    {
        if ($this->deleteConfirm !== 'delete') {
            $this->addError('deleteConfirm', 'Type "delete" to confirm.');
            return;
        }

        $this->project->delete();
        $this->redirectRoute('dashboard', navigate: true);
    }

    private function buildDefaultAbilities(): void
    {
        $this->tokenAbilities = [
            'tasks:read'    => false,
            'tasks:create'  => false,
            'tasks:update'  => false,
            'tasks:resolve' => false,
        ];
    }

    public function render()
    {
        return view('livewire.projects.project-settings')
            ->layout('layouts.app', ['title' => "{$this->project->name} Settings"]);
    }
}
