<?php

namespace App\Console\Commands;

use App\Enums\TaskStatusEnum;
use App\Services\TaskService;
use Illuminate\Console\Command;
use App\Jobs\SendTaskReminderEmail;

class SendNotificationForComingTasksCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:notify-for-coming';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notifications for tasks due in 24 hours';

    /**
     * Execute the console command.
     */
    protected function upcomingTasks()
    {
        return (new TaskService())->get(conditions: [
            ['due_date', '>=', now()->addHours(23)],
            ['due_date', '<=', now()->addHours(25)],
            ['status', '!=', TaskStatusEnum::COMPLETED]
        ]);
    }

    public function handle()
    {
        $this->info('Before dispatching coming task notifications...');

        foreach ($this->upcomingTasks() as $task) {
            SendTaskReminderEmail::dispatch($task);
        }

        $this->info('After coming task notifications dispatched successfully.');
    }
}
