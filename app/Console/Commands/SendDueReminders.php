<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Notifications\TaskDueReminder;
use Illuminate\Console\Command;

class SendDueReminders extends Command
{
    protected $signature = 'tasks:send-reminders {--days=1 : Days ahead to check}';
    protected $description = 'Send due-date reminder emails for upcoming tasks';

    public function handle(): void
    {
        $days = (int) $this->option('days');

        $tasks = Task::with(['assignee', 'project'])
            ->whereNotNull('due_date')
            ->whereNull('resolved_at')
            ->whereDate('due_date', now()->addDays($days)->toDateString())
            ->whereNotNull('assignee_id')
            ->get();

        foreach ($tasks as $task) {
            $task->assignee->notify(new TaskDueReminder($task));
        }

        $this->info("Sent {$tasks->count()} reminder(s).");
    }
}
