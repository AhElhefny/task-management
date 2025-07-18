<?php

namespace App\Services;

use App\Enums\UserRoleEnum;
use App\Models\Task;

class TaskService extends BaseService
{
    /**
     * Create a new class instance.
     */
    protected $model;

    public function __construct()
    {
        $this->model = Task::class;
    }

    public function getTasksAccordingToUserRole($user, $with = [], $paginateNum = 10, $scopes = [])
    {
        try {
            return $this->model::query()
                ->with($with)
                ->when($user->role->value == UserRoleEnum::USER->value, function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->when(!empty($scopes), function ($query) use ($scopes) {
                    $this->applyScopes($query, $scopes);
                })
                ->paginate($paginateNum);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getTaskAccordingToUserRole($user, $id, $with = [])
    {
        try {
            return $this->model::query()
                ->with($with)
                ->when($user->role->value == UserRoleEnum::USER->value, function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->findOrFail($id);
        } catch (\Exception $e) {
            throw new \Exception('You are not authorized to view this task');
        }
    }

    public function assignUserToTask($task, $user)
    {
        try {
            $task->update(['user_id' => $user->id]);
            return $task->refresh();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Add a dependency to a task.
     *
     * @param Task $task
     * @param array $request
     * @return Task
     * @throws \Exception
     */
    public function addDependency(Task $task, array $request): Task
    {
        $task->dependencies()->attach($request['dependency_ids']);
        $task->load('dependencies');
        return $task;
    }

    /**
     * Remove a dependency from a task.
     *
     * @param Task $task
     * @param int $dependencyId
     * @return Task
     * @throws \Exception
     */
    public function removeDependency(Task $task, int $dependencyId): Task
    {
        // Check if the dependency exists
        if (!$task->dependencies()->where('dependency_id', $dependencyId)->exists()) {
            throw new \Exception('This dependency does not exist');
        }
        $task->dependencies()->detach($dependencyId);
        $task->load('dependencies');
        return $task;
    }
}
