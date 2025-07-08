<?php

namespace App\Rules;

use App\Services\TaskService;
use Illuminate\Contracts\Validation\Rule;

class CheckIfItRepeatedDependency implements Rule
{
    protected $taskId, $taskService;

    public function __construct($taskId)
    {
        $this->taskId = $taskId;
        $this->taskService = new TaskService();
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Check if the task it self-dependency
        if ($value == $this->taskId) {
            return false;
        }
        // Check if the task and dependency record is repeated
        $task = $this->taskService->find($this->taskId);
        if ($task && $task->dependencies()->where('dependency_id', $value)->exists()) {
            return false;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        // Extract index from attribute if possible
        $attribute = request()->route() ? request()->route()->parameter('task') : null;
        return 'This dependency is invalid or repeated.';
    }
}
