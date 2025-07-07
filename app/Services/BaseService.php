<?php

namespace App\Services;

class BaseService
{
    /**
     * Create a new class instance.
     */
    protected $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * Retrieve a paginated list of tasks based on given conditions and scopes.
     *
     * @param int $pagination_num The number of items per page for pagination.
     * @param array $conditions An associative array of conditions to filter the query.
     * @param array $scopes An array of model scopes to apply to the query.
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator The paginated result set.
     */

    public function limit(int $pagination_num = 10, array $conditions = [], array $scopes = [])
    {
        try {
            $query = $this->model::query();
            return $query->when(!empty($conditions), function ($query) use ($conditions) {
                $query->where($conditions);
            })
                ->when(!empty($scopes), function ($query) use ($scopes) {
                    $this->applyScopes($query, $scopes);
                })
                ->paginate($pagination_num);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    protected function applyScopes($query, $scopes)
    {
        foreach ($scopes as $scope) {
            if (method_exists($this->model, 'scope' . ucfirst($scope))) {
                $query->$scope();
            }
        }
        return $query;
    }

    public function get($conditions = [], $scopes = [])
    {
        try {
            $query = $this->model::query();
            return $query
                ->when(!empty($conditions), function ($query) use ($conditions) {
                    $query->where($conditions);
                })
                ->when(!empty($scopes), function ($query) use ($scopes) {
                    foreach ($scopes as $scope) {
                        if (method_exists($this->model, 'scope' . ucfirst($scope))) {
                            $query->$scope();
                        }
                    }
                })
                ->get();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function create($data)
    {
        try {
            $task = $this->model::create($data);
            return $task->refresh();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function first($conditions = [])
    {
        return $this->model::where($conditions)->firstOrFail();
    }

    public function find(int $id)
    {
        return $this->model::findOrFail($id);
    }

    public function edit($model, $data)
    {
        try {
            $model->update($data);
            return $model->refresh();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function delete($model)
    {
        try {
            return $model->delete();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
