<?php

namespace App\Jobs;

use App\Console\Commands\SendNotificationForComingTasksCommand;
use App\Models\Task;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendTaskReminderEmail implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    protected Task $task;
    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->task->user) {
            $this->task->user->notify(new SendNotificationForComingTasksCommand($this->task));
        }
    }
}
