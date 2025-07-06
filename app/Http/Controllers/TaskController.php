<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatusEnum;
use App\Enums\UserRoleEnum;
use App\Http\Requests\AddTaskDependencyRequest;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskStatusRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Services\TaskService;
use App\Traits\HelperTrait;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    use HelperTrait;

    public function __construct(protected TaskService $taskService)
    {
        $this->middleware('auth:api');
        // Only managers can create or update tasks
        $this->middleware('role:' . UserRoleEnum::MANAGER->value)->only(['store', 'update', 'destroy']);
    }

    /**
     * Get all tasks with sorting and filtering.
     *
     * @queryParam status string One of: pending, in_progress, completed, overdue. Example: pending
     * @queryParam due_date_range string Date range in the format of "YYYY-MM-DD,YYYY-MM-DD". Example: 2023-03-01,2023-03-31
     * @queryParam text_search string Search query. Example: task
     * @queryParam sorting string One of: priority, due_date, created_at. Example: priority
     * @queryParam assignee_id integer Filter tasks by assignee. Example: 1
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        $scopes = ['status', 'dueDateRange', 'textSearch', 'sorting'];

        // Regular users can only see tasks assigned to them
        if ($user->role === UserRoleEnum::USER) {
            $tasks = $this->taskService->limit(
                scopes: $scopes,
                conditions: ['assignee_id' => $user->id]
            );
        } else {
            // Managers can see all tasks and filter by assignee
            $scopes[] = 'assignedTo';
            $tasks = $this->taskService->limit(scopes: $scopes);
        }

        return TaskResource::collection($tasks)
            ->additional([
                'message' => 'Tasks retrieved successfully',
                'status' => Response::HTTP_OK,
            ]);
    }


    /**
     * Store a newly created task in storage.
     *
     * @param  \App\Http\Requests\Task\StoreTaskRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTaskRequest $request)
    {
        $task = $this->taskService->create(data: $request->validated());
        return $this->apiResponse(
            msg: 'Task created successfully',
            data: TaskResource::make($task),
            code: Response::HTTP_CREATED
        );
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Task  $task
     * @param  \App\Http\Requests\Task\StoreTaskRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Task $task, StoreTaskRequest $request)
    {
        $task = $this->taskService->edit(model: $task, data: $request->validated());
        return $this->apiResponse(
            msg: 'Task updated successfully',
            data: TaskResource::make($task),
            code: Response::HTTP_OK
        );
    }

    /**
     * Display the specified task.
     *
     * @param \App\Models\Task $task The task instance to be displayed.
     * @return \Illuminate\Http\Response The response containing the task data.
     */
    public function show(Task $task)
    {
        $user = Auth::user();

        // Regular users can only view tasks assigned to them
        if ($user->role === UserRoleEnum::USER && $task->assignee_id !== $user->id) {
            return $this->apiResponse(
                msg: 'You are not authorized to view this task',
                code: Response::HTTP_FORBIDDEN
            );
        }

        // Load dependencies
        $task->load('dependencies');

        return $this->apiResponse(
            msg: 'Task retrieved successfully',
            data: TaskResource::make($task),
            code: Response::HTTP_OK
        );
    }


    /**
     * Remove the specified task from storage.
     *
     * @param \App\Models\Task $task The task instance to be deleted.
     * @return \Illuminate\Http\Response The response containing the task deletion status.
     */
    public function destroy(Task $task)
    {
        $this->taskService->delete($task);
        return $this->apiResponse(
            msg: 'Task deleted successfully',
            code: Response::HTTP_OK
        );
    }

    /**
     * Update the status of a task.
     *
     * @param \App\Models\Task $task The task instance to be updated.
     * @param \App\Http\Requests\Task\UpdateTaskStatusRequest $request The request instance containing the new task status.
     *
     * @return \Illuminate\Http\Response The response containing the task with the updated status.
     */
    public function updateStatus(Task $task, UpdateTaskStatusRequest $request)
    {
        $user = Auth::user();

        // Regular users can only update status of tasks assigned to them
        if ($user->role === UserRoleEnum::USER && $task->assignee_id !== $user->id) {
            return $this->apiResponse(
                msg: 'You are not authorized to update this task',
                code: Response::HTTP_FORBIDDEN
            );
        }

        // Check if trying to complete a task with incomplete dependencies
        if ($request->status === TaskStatusEnum::COMPLETED->value && !$task->areDependenciesCompleted()) {
            return $this->apiResponse(
                msg: 'Cannot complete this task until all dependencies are completed',
                code: Response::HTTP_BAD_REQUEST
            );
        }

        $task = $this->taskService->edit($task, $request->validated());
        return $this->apiResponse(
            msg: 'Task status updated successfully',
            data: TaskResource::make($task),
            code: Response::HTTP_OK
        );
    }

    /**
     * Add a dependency to a task.
     *
     * @param \App\Models\Task $task The task to add a dependency to.
     * @param \App\Http\Requests\AddTaskDependencyRequest $request The request containing the dependency ID.
     * @return \Illuminate\Http\Response
     */
    public function addDependency(Task $task, AddTaskDependencyRequest $request)
    {
        $dependencyId = $request->validated()['dependency_id'];

        // Check if the dependency already exists
        if ($task->dependencies()->where('dependency_id', $dependencyId)->exists()) {
            return $this->apiResponse(
                msg: 'This dependency already exists',
                code: Response::HTTP_BAD_REQUEST
            );
        }

        $task->dependencies()->attach($dependencyId);
        $task->load('dependencies');

        return $this->apiResponse(
            msg: 'Dependency added successfully',
            data: TaskResource::make($task),
            code: Response::HTTP_OK
        );
    }

    /**
     * Remove a dependency from a task.
     *
     * @param \App\Models\Task $task The task to remove a dependency from.
     * @param int $dependencyId The ID of the dependency to remove.
     * @return \Illuminate\Http\Response
     */
    public function removeDependency(Task $task, int $dependencyId)
    {
        // Check if the dependency exists
        if (!$task->dependencies()->where('dependency_id', $dependencyId)->exists()) {
            return $this->apiResponse(
                msg: 'This dependency does not exist',
                code: Response::HTTP_BAD_REQUEST
            );
        }

        $task->dependencies()->detach($dependencyId);
        $task->load('dependencies');

        return $this->apiResponse(
            msg: 'Dependency removed successfully',
            data: TaskResource::make($task),
            code: Response::HTTP_OK
        );
    }
}
