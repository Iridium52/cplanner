<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskDueReminder extends Notification
{
    use Queueable;

    public function __construct(public readonly Task $task)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $daysLeft = now()->diffInDays($this->task->due_date, false);
        $label = $daysLeft === 0 ? 'due today' : "due in {$daysLeft} day(s)";

        return (new MailMessage)
            ->subject("[{$this->task->task_number}] Task {$label}: {$this->task->title}")
            ->greeting("Hi {$notifiable->name},")
            ->line("A task assigned to you is {$label}.")
            ->line("**{$this->task->task_number}**: {$this->task->title}")
            ->line("Project: {$this->task->project->name}")
            ->action('View Task', url("/projects/{$this->task->project_id}?task={$this->task->id}"))
            ->line('C Planner — your project management hub.');
    }
}
