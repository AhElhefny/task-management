<?php

namespace App\Http\Requests\Task;

use App\Enums\TaskStatusEnum;
use App\Traits\HelperTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskStatusRequest extends FormRequest
{
    use HelperTrait;

    protected $stopOnFirstFailure = true;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only managers can create or update tasks
        return $this->user() && $this->user()->isNormal() && $this->user()->tokenCan('task:update-status');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'numeric', Rule::in(array_column(TaskStatusEnum::cases(), 'value'))],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $task = $this->route('task');
            if (!isset($task->user_id) || $task->user_id != $this->user()->id) {
                $validator->errors()->add('status', 'You are not authorized to update this task');
            }
            if ($task->status->value == $this->status) {
                $validator->errors()->add('status', 'Task status cannot be the same');
            }
        });
    }
}
