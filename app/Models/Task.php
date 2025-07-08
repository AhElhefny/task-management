<?php

namespace App\Models;

use App\Enums\TaskStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'status',
        'due_date',
        'user_id',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'status'   => TaskStatusEnum::class,
    ];

    /**
     * Get the user that is assigned to the task.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the tasks that this task depends on.
     */
    public function dependencies(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'task_id', 'dependency_id');
    }


    /**
     * Scope a query to only include tasks of a given status.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStatus($query)
    {
        if (!request()->has('status') || !in_array(request('status'), array_column(TaskStatusEnum::cases(), 'value'))) {
            return $query;
        }
        return $query->where('status', request('status'));
    }

    /**
     * Scope a query to only include tasks due within a given date range.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDueDateRange($query)
    {
        if (request('start_date') && request('end_date')) {
            return $query->whereBetween('due_date', [request('start_date'), request('end_date')]);
        }
        return $query;
    }

    /**
     * Scope a query to filter by text search for title and description and order by relevance.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTextSearch($query)
    {
        if (!request()->has('text_search') || is_null(request('text_search'))) {
            return $query;
        }
        return $query->selectRaw('*, MATCH(title, description) AGAINST (?) as relevance', [request('text_search')])
            ->whereRaw('MATCH(title, description) AGAINST (? IN NATURAL LANGUAGE MODE)', [request('text_search')])
            ->orderByDesc('relevance');
    }


    /**
     * Scope a query to only include tasks assigned to a specific user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|null $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAssignedTo($query, $userId = null)
    {
        if (is_null($userId) && !request()->has('user_id')) {
            return $query;
        }

        $userId ??= request('user_id');
        return $query->where('user_id', $userId);
    }

    /**
     * Check if all dependencies are completed.
     *
     * @return bool
     */
    public function areDependenciesCompleted(): bool
    {
        if ($this->dependencies->isEmpty()) {
            return true;
        }

        foreach ($this->dependencies as $dependency) {
            if ($dependency->status !== TaskStatusEnum::COMPLETED) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if all dependencies are completed or cancelled.
     *
     * @return bool
     */
    public function areDependenciesCompletedOrCancelled(): bool
    {
        if ($this->dependencies->isEmpty()) {
            return true;
        }
        foreach ($this->dependencies as $dependency) {
            if (!in_array($dependency->status, [TaskStatusEnum::COMPLETED, TaskStatusEnum::CANCELLED])) {
                return false;
            }
        }
        return true;
    }
}
