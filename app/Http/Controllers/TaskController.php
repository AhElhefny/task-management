<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddTaskDependencyRequest;
use App\Http\Requests\Task\{AssignUserToTaskRequest, StoreTaskRequest, UpdateTaskRequest, UpdateTaskStatusRequest};
use App\Http\Resources\TaskResource;
use App\Models\{Task, User};
use App\Services\{BaseService, TaskService};
use App\Traits\HelperTrait;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    use HelperTrait;

    public function __construct(protected TaskService $taskService) {}

    public function index()
    {
        $scopes = ['status', 'dueDateRange', 'textSearch', 'assignedTo'];

        $tasks = $this->taskService->getTasksAccordingToUserRole(
            user: Auth::user(),
            with: ['dependencies'],
            paginateNum: 15,
            scopes: $scopes
        );
        return TaskResource::collection($tasks)
            ->additional([
                'message' => 'Tasks retrieved successfully',
                'status'  => Response::HTTP_OK,
            ]);
    }

    public function store(StoreTaskRequest $request)
    {
        $task = $this->taskService->create(data: $request->validated());
        return $this->apiResponse(
            msg: 'Task created successfully',
            data: TaskResource::make($task),
            code: Response::HTTP_CREATED
        );
    }

    public function update(Task $task, UpdateTaskRequest $request)
    {
        $task = $this->taskService->edit(model: $task, data: $request->validated());
        return $this->apiResponse(
            msg: 'Task updated successfully',
            data: TaskResource::make($task),
        );
    }


    public function show(Task $task)
    {
        $task = $this->taskService->getTaskAccordingToUserRole(user: Auth::user(), id: $task->id, with: ['dependencies']);
        return $this->apiResponse(
            msg: 'Task retrieved successfully',
            data: TaskResource::make($task),
        );
    }

    public function assignUserToTask(Task $task, AssignUserToTaskRequest $request)
    {
        $user = (new BaseService(User::class))->find($request->user_id);
        $task = $this->taskService->assignUserToTask($task, $user);
        return $this->apiResponse(
            msg: 'User assigned to task successfully',
            data: TaskResource::make($task),
        );
    }

    public function updateStatus(Task $task, UpdateTaskStatusRequest $request)
    {
        $task = $this->taskService->edit(model: $task, data: $request->validated());
        return $this->apiResponse(
            msg: 'Task status updated successfully',
            data: TaskResource::make($task),
        );
    }


    public function addDependency(Task $task, AddTaskDependencyRequest $request)
    {
        try {
            $task = $this->taskService->addDependency($task, $request->validated());
            return $this->apiResponse(
                msg: 'Dependency added successfully',
                data: TaskResource::make($task),
                code: Response::HTTP_OK
            );
        } catch (\Exception $e) {
            return $this->apiResponse(
                error: true,
                msg: $e->getMessage(),
                errors: ['line' => $e->getLine(), 'file' => $e->getFile()],
                code: Response::HTTP_BAD_REQUEST
            );
        }
    }

}
