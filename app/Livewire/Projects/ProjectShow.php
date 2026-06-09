<?php

namespace App\Livewire\Projects;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskComment;
use App\Notifications\TaskStatusChanged;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProjectShow extends Component
{
    use WithFileUploads;

    public Project $project;
    public string $view = 'kanban'; // kanban | list
    public string $filterType = '';
    public string $filterPriority = '';
    public array $filterCategories = [];
    public string $search = '';

    // Export modal
    public bool $showExportModal = false;
    public string $exportFormat = 'excel';
    public array $exportTypes = [];
    public array $exportPriorities = [];
    public array $exportStatuses = [];

    // List-view specific
    public string $listFilterStatus = 'open'; // 'open' | 'all' | numeric status_id
    public string $listSortColumn = 'position';
    public string $listSortDir = 'asc';

    // Task detail modal
    public ?Task $selectedTask = null;
    public bool $showTaskModal = false;
    public string $commentBody = '';
    public string $editTitle = '';
    public string $editDescription = '';
    public bool $descriptionSaved = false;
    public bool $showDeleteConfirm = false;
    public string $deleteConfirmText = '';

    // New task form
    public bool $showNewTaskModal = false;
    public string $newTaskTitle = '';
    public string $newTaskType = 'feature';
    public string $newTaskPriority = 'medium';
    public ?int $newTaskStatusId = null;
    public $attachment;

    public function mount(Project $project): void
    {
        $this->project = $project->load(['statuses', 'categories']);
    }

    private function freshRelations(): array
    {
        return ['status', 'categories', 'assignee', 'reporter', 'comments.user', 'attachments.user'];
    }

    private function baseTaskQuery()
    {
        return $this->project->tasks()
            ->with(['status', 'categories', 'assignee:id,name,avatar_color', 'reporter:id,name'])
            ->withCount('attachments')
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->when($this->filterPriority, fn($q) => $q->where('priority', $this->filterPriority))
            ->when($this->filterCategories, fn($q) => $q->whereHas('categories', fn($q2) =>
                $q2->whereIn('project_categories.id', $this->filterCategories)
            ))
            ->when($this->search, fn($q) => $q->where(function ($q2) {
                $q2->where('title', 'like', "%{$this->search}%")
                   ->orWhere('task_number', 'like', "%{$this->search}%");
            }));
    }

    private function getKanbanTasks()
    {
        return $this->baseTaskQuery()
            ->orderBy('position')
            ->get()
            ->groupBy('status_id');
    }

    private function getListTasks()
    {
        $tasks = $this->baseTaskQuery()
            ->when($this->listFilterStatus === 'open', fn($q) =>
                $q->whereHas('status', fn($q2) => $q2->where('is_done', false))
            )
            ->when(is_numeric($this->listFilterStatus), fn($q) =>
                $q->where('status_id', (int) $this->listFilterStatus)
            )
            ->get();

        $sorted = match ($this->listSortColumn) {
            'title'       => $tasks->sortBy('title', SORT_NATURAL | SORT_FLAG_CASE),
            'task_number' => $tasks->sortBy('task_number'),
            'status'      => $tasks->sortBy(fn($t) => $t->status?->name ?? ''),
            'type'        => $tasks->sortBy('type'),
            'priority'    => $tasks->sortBy(fn($t) => match ($t->priority) {
                                'critical' => 0, 'high' => 1, 'medium' => 2, 'low' => 3, default => 4
                            }),
            'assignee'    => $tasks->sortBy(fn($t) => $t->assignee?->name ?? "\xff"),
            'due_date'    => $tasks->sortBy('due_date'),
            default       => $tasks->sortBy('position'),
        };

        return $this->listSortDir === 'desc' ? $sorted->reverse()->values() : $sorted->values();
    }

    public function sortList(string $column): void
    {
        if ($this->listSortColumn === $column) {
            $this->listSortDir = $this->listSortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->listSortColumn = $column;
            $this->listSortDir = 'asc';
        }
    }

    public function openTask(int $taskId): void
    {
        $this->selectedTask = Task::with($this->freshRelations())->findOrFail($taskId);
        $this->editTitle = $this->selectedTask->title;
        $this->editDescription = $this->selectedTask->description ?? '';
        $this->descriptionSaved = false;
        $this->commentBody = '';
        $this->resetErrorBag();
        $this->showTaskModal = true;
    }

    public function closeTask(): void
    {
        $this->showTaskModal = false;
        $this->selectedTask = null;
        $this->commentBody = '';
        $this->editTitle = '';
        $this->editDescription = '';
        $this->descriptionSaved = false;
        $this->showDeleteConfirm = false;
        $this->deleteConfirmText = '';
        $this->resetErrorBag();
    }

    public function deleteTask(): void
    {
        if ($this->deleteConfirmText !== 'delete') {
            $this->addError('deleteConfirmText', 'Type "delete" to confirm.');
            return;
        }

        $this->selectedTask->delete();
        $this->closeTask();
    }

    public function saveTitle(): void
    {
        if (!$this->selectedTask) return;
        $this->validate(['editTitle' => 'required|string|max:255']);
        $this->selectedTask->update(['title' => $this->editTitle]);
        $this->selectedTask = $this->selectedTask->fresh($this->freshRelations());
    }

    public function saveDescription(): void
    {
        if (!$this->selectedTask) return;
        $this->selectedTask->update(['description' => $this->editDescription]);
        $this->closeTask();
    }

    public function createTask(): void
    {
        $this->validate([
            'newTaskTitle'    => 'required|string|max:255',
            'newTaskType'     => 'required|in:bug,feature,improvement,chore,question',
            'newTaskPriority' => 'required|in:critical,high,medium,low',
        ]);

        $statusId = $this->newTaskStatusId
            ?? $this->project->statuses()->where('is_default', true)->value('id')
            ?? $this->project->statuses()->value('id');

        $task = $this->project->tasks()->create([
            'title'       => $this->newTaskTitle,
            'type'        => $this->newTaskType,
            'priority'    => $this->newTaskPriority,
            'status_id'   => $statusId,
            'reporter_id' => auth()->id(),
            'position'    => Task::where('project_id', $this->project->id)->max('position') + 1,
        ]);

        ActivityLog::record($task, 'Task created');

        $this->reset(['newTaskTitle', 'newTaskType', 'newTaskPriority', 'newTaskStatusId', 'showNewTaskModal']);
        $this->openTask($task->id);
    }

    public function updateTaskStatus(int $taskId, int $statusId): void
    {
        if (!auth()->user()->isAdmin()) return;

        $task = Task::findOrFail($taskId);
        $oldStatus = $task->status;
        $newStatus = ProjectStatus::findOrFail($statusId);

        $task->update([
            'status_id'   => $statusId,
            'resolved_at' => $newStatus->is_done ? now() : null,
        ]);

        if ($task->assignee) {
            try {
                $task->assignee->notify(new TaskStatusChanged($task, $oldStatus, $newStatus));
            } catch (\Throwable) {
                // Notification failure must not abort a status update
            }
        }
        ActivityLog::record($task, "Status changed to {$newStatus->name}");

        if ($this->selectedTask && $this->selectedTask->id === $taskId) {
            $this->selectedTask = $task->fresh($this->freshRelations());
        }
    }

    public function updateTaskPositions(array $positions): void
    {
        if (!auth()->user()->isAdmin()) return;

        foreach ($positions as $item) {
            Task::where('id', $item['id'])->update([
                'status_id' => $item['status_id'],
                'position'  => $item['position'],
            ]);
        }
    }

    public function addComment(): void
    {
        $this->validate(['commentBody' => 'required|string|max:5000']);

        TaskComment::create([
            'task_id' => $this->selectedTask->id,
            'user_id' => auth()->id(),
            'body'    => $this->commentBody,
        ]);

        $this->commentBody = '';
        $this->selectedTask = $this->selectedTask->fresh($this->freshRelations());
    }

    public function updateTaskField(string $field, mixed $value): void
    {
        if (!$this->selectedTask || !auth()->user()->isAdmin()) return;

        $allowed = ['title', 'description', 'type', 'priority', 'assignee_id', 'due_date', 'status_id'];
        if (!in_array($field, $allowed)) return;

        if ($field === 'status_id') {
            $this->updateTaskStatus($this->selectedTask->id, (int) $value);
            return;
        }

        $this->selectedTask->update([$field => $value]);
        $this->selectedTask = $this->selectedTask->fresh($this->freshRelations());
    }

    public function inlineUpdateTask(int $taskId, string $field, mixed $value): void
    {
        if (!auth()->user()->isAdmin()) return;

        $allowed = ['status_id', 'type', 'priority', 'assignee_id', 'due_date'];
        if (!in_array($field, $allowed)) return;

        $task = Task::where('id', $taskId)->where('project_id', $this->project->id)->firstOrFail();

        if ($field === 'status_id') {
            $oldStatus = $task->status;
            $newStatus = ProjectStatus::findOrFail((int) $value);
            $task->update([
                'status_id'   => $value,
                'resolved_at' => $newStatus->is_done ? now() : null,
            ]);
            if ($task->assignee) {
                try {
                    $task->assignee->notify(new TaskStatusChanged($task, $oldStatus, $newStatus));
                } catch (\Throwable) {
                    // Notification failure must not abort a status update
                }
            }
        } else {
            $task->update([$field => $value ?: null]);
        }
    }

    public function quickCreateTask(string $title, int $statusId): void
    {
        if (!auth()->user()->isAdmin()) return;
        $title = trim($title);
        if (!$title) return;

        $task = $this->project->tasks()->create([
            'title'       => $title,
            'type'        => 'feature',
            'priority'    => 'medium',
            'status_id'   => $statusId,
            'reporter_id' => auth()->id(),
            'position'    => Task::where('project_id', $this->project->id)->max('position') + 1,
        ]);

        ActivityLog::record($task, 'Task created');
        $this->openTask($task->id);
    }

    public function toggleTaskCategory(int $categoryId): void
    {
        if (!auth()->user()->isAdmin()) return;
        if (!$this->selectedTask) return;

        $this->selectedTask->categories()->toggle($categoryId);
        $this->selectedTask = $this->selectedTask->fresh($this->freshRelations());
    }

    public function toggleCategoryFilter(int $categoryId): void
    {
        if (in_array($categoryId, $this->filterCategories)) {
            $this->filterCategories = array_values(array_filter(
                $this->filterCategories, fn($id) => $id !== $categoryId
            ));
        } else {
            $this->filterCategories[] = $categoryId;
        }
    }

    public function uploadAttachment(): void
    {
        if (!$this->selectedTask) return;

        $this->validate(['attachment' => 'required|file|max:20480|mimes:jpeg,jpg,png,gif,webp,svg,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip']);

        $path = $this->attachment->store('task-attachments', 'local');

        TaskAttachment::create([
            'task_id'   => $this->selectedTask->id,
            'user_id'   => auth()->id(),
            'filename'  => $this->attachment->getClientOriginalName(),
            'path'      => $path,
            'size'      => $this->attachment->getSize(),
            'mime_type' => $this->attachment->getMimeType(),
        ]);

        $this->attachment = null;
        $this->selectedTask = $this->selectedTask->fresh($this->freshRelations());
    }

    public function deleteAttachment(int $id): void
    {
        if (!auth()->user()->isAdmin()) return;

        $att = TaskAttachment::where('id', $id)
            ->where('task_id', $this->selectedTask?->id)
            ->firstOrFail();

        Storage::disk('local')->delete($att->path);
        $att->delete();

        $this->selectedTask = $this->selectedTask->fresh($this->freshRelations());
    }

    public function toggleFlag(string $flag): void
    {
        if (!auth()->user()->isAdmin()) return;
        if (!in_array($flag, ['needs_discussion', 'needs_action'])) return;
        if (!$this->selectedTask) return;

        $this->selectedTask->update([$flag => !$this->selectedTask->$flag]);
        $this->selectedTask = $this->selectedTask->fresh($this->freshRelations());
    }

    public function openExportModal(): void
    {
        $this->exportTypes = [];
        $this->exportPriorities = [];
        $this->exportStatuses = [];
        $this->exportFormat = 'excel';
        $this->showExportModal = true;
    }

    public function closeExportModal(): void
    {
        $this->showExportModal = false;
    }

    public function doExport(): void
    {
        $params = ['format' => $this->exportFormat];
        if ($this->exportTypes)      { $params['types']      = $this->exportTypes; }
        if ($this->exportPriorities) { $params['priorities'] = $this->exportPriorities; }
        if ($this->exportStatuses)   { $params['statuses']   = $this->exportStatuses; }

        $url = route('projects.export', $this->project) . '?' . http_build_query($params);
        $this->showExportModal = false;
        $this->dispatch('download-export', url: $url);
    }

    public function render()
    {
        $statuses = $this->project->statuses;
        $users    = \App\Models\User::all(['id', 'name', 'avatar_color']);

        if ($this->view === 'list') {
            $tasksByStatus = collect();
            $listTasks     = $this->getListTasks();
        } else {
            $tasksByStatus = $this->getKanbanTasks();
            $listTasks     = collect();
        }

        return view('livewire.projects.project-show', compact('tasksByStatus', 'listTasks', 'statuses', 'users'))
            ->layout('layouts.app', ['title' => $this->project->name]);
    }
}
