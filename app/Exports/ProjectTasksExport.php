<?php

namespace App\Exports;

use App\Models\Project;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class ProjectTasksExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    public function __construct(
        private Project $project,
        private array $types,
        private array $priorities,
        private array $statuses = [],
    ) {}

    public function collection()
    {
        return $this->project->tasks()
            ->with(['status', 'categories', 'assignee:id,name'])
            ->when($this->types, fn($q) => $q->whereIn('type', $this->types))
            ->when($this->priorities, fn($q) => $q->whereIn('priority', $this->priorities))
            ->when($this->statuses, fn($q) => $q->whereIn('status_id', $this->statuses))
            ->orderBy('position')
            ->get();
    }

    public function headings(): array
    {
        return ['Task#', 'Title', 'Description', 'Type', 'Priority', 'Status', 'Assignee', 'Due Date', 'Categories'];
    }

    public function map($task): array
    {
        return [
            $task->task_number,
            $task->title,
            $task->description ?? '',
            ucfirst($task->type),
            ucfirst($task->priority),
            $task->status->name,
            $task->assignee?->name ?? '',
            $task->due_date?->format('Y-m-d') ?? '',
            $task->categories->pluck('name')->join(', '),
        ];
    }

    public function title(): string
    {
        return Str::limit($this->project->name, 31);
    }
}
