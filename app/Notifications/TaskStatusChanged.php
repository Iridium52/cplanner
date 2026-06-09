<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\ProjectStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Task $task,
        public readonly ProjectStatus $oldStatus,
        public readonly ProjectStatus $newStatus,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("[{$this->task->task_number}] Status changed: {$this->oldStatus->name} → {$this->newStatus->name}")
            ->greeting("Hi {$notifiable->name},")
            ->line("A task's status has been updated.")
            ->line("**{$this->task->task_number}**: {$this->task->title}")
            ->line("{$this->oldStatus->name} → **{$this->newStatus->name}**")
            ->action('View Task', url("/projects/{$this->task->project_id}?task={$this->task->id}"))
            ->line('C Planner — your project management hub.');
    }
}
