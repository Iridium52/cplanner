<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Task;
use App\Notifications\TaskStatusChanged;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    public function index(Request $request, Project $project): JsonResponse
    {
        $this->authorizeAbility($request, $project, 'tasks:read');

        $tasks = $project->tasks()
            ->with(['status', 'category', 'assignee:id,name', 'reporter:id,name'])
            ->when($request->get('type'), fn($q, $type) => $q->where('type', $type))
            ->when($request->get('status'), fn($q, $s) => $q->whereHas('status', fn($q2) => $q2->where('name', $s)))
            ->orderBy('position')
            ->get();

        return response()->json(['data' => $tasks]);
    }

    public function store(Request $request, Project $project): JsonResponse
    {
        $this->authorizeAbility($request, $project, 'tasks:create');

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'type'        => ['required', Rule::in(['bug', 'feature', 'improvement', 'chore', 'question'])],
            'priority'    => ['nullable', Rule::in(['critical', 'high', 'medium', 'low'])],
            'due_date'    => 'nullable|date',
        ]);

        $defaultStatus = $project->statuses()->where('is_default', true)->first()
            ?? $project->statuses()->first();

        abort_unless($defaultStatus, 422, 'Project has no statuses configured.');

        $task = $project->tasks()->create([
            ...$validated,
            'status_id'   => $defaultStatus->id,
            'reporter_id' => $request->user()->id,
        ]);

        ActivityLog::record($task, 'Task created via API', ['source' => 'api']);

        return response()->json(['data' => $task->load('status')], 201);
    }

    public function show(Request $request, Task $task): JsonResponse
    {
        $this->authorizeAbility($request, $task->project, 'tasks:read');

        return response()->json([
            'data' => $task->load('status', 'category', 'assignee:id,name', 'reporter:id,name', 'comments.user:id,name'),
        ]);
    }

    public function update(Request $request, Task $task): JsonResponse
    {
        $this->authorizeAbility($request, $task->project, 'tasks:update');

        $validated = $request->validate([
            'title'       => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'priority'    => ['sometimes', Rule::in(['critical', 'high', 'medium', 'low'])],
            'due_date'    => 'sometimes|nullable|date',
            'status_id'   => 'sometimes|integer|exists:project_statuses,id',
        ]);

        if (isset($validated['status_id']) && $validated['status_id'] !== $task->status_id) {
            $oldStatus = $task->status;
            $task->update($validated);
            $newStatus = $task->fresh()->status;

            // Notify assignee/reporter on status change
            if ($task->assignee) {
                $task->assignee->notify(new TaskStatusChanged($task, $oldStatus, $newStatus));
            }
            ActivityLog::record($task, "Status changed to {$newStatus->name} via API");
        } else {
            $task->update($validated);
        }

        return response()->json(['data' => $task->fresh()->load('status')]);
    }

    public function resolve(Request $request, Task $task): JsonResponse
    {
        $this->authorizeAbility($request, $task->project, 'tasks:resolve');

        $doneStatus = $task->project->statuses()->where('is_done', true)->first();
        abort_unless($doneStatus, 422, 'Project has no "done" status configured.');

        $task->update([
            'status_id'   => $doneStatus->id,
            'resolved_at' => now(),
        ]);

        ActivityLog::record($task, 'Task resolved via API');

        return response()->json(['data' => $task->fresh()->load('status')]);
    }

    public function reopen(Request $request, Task $task): JsonResponse
    {
        $this->authorizeAbility($request, $task->project, 'tasks:update');

        $defaultStatus = $task->project->statuses()->where('is_default', true)->first()
            ?? $task->project->statuses()->first();

        $task->update([
            'status_id'   => $defaultStatus->id,
            'resolved_at' => null,
        ]);

        ActivityLog::record($task, 'Task reopened via API');

        return response()->json(['data' => $task->fresh()->load('status')]);
    }

    private function authorizeAbility(Request $request, Project $project, string $action): void
    {
        $token = $request->user()->currentAccessToken();
        $abilities = $token->abilities;

        $allowed = collect($abilities)->contains(fn($a) =>
            $a === '*' ||
            $a === "*:{$action}" ||
            $a === "project:{$project->id}:{$action}" ||
            $a === "project:{$project->id}:*"
        );

        abort_unless($allowed, 403, "Token lacks ability '{$action}' on project {$project->id}.");
    }
}
