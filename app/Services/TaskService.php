<?php

namespace App\Services;

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
}
