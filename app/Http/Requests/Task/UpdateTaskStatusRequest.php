<?php

namespace App\Http\Requests\Task;

use App\Enums\TaskStatusEnum;
use App\Traits\HelperTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskStatusRequest extends FormRequest
{
    use HelperTrait;

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

    public function prepareForValidation(): void
    {
        $oldExpectedStatus = match ((int)$this->status) {
            TaskStatusEnum::IN_PROGRESS->value => TaskStatusEnum::PENDING->value,
            TaskStatusEnum::COMPLETED->value => TaskStatusEnum::IN_PROGRESS->value,
            TaskStatusEnum::OVERDUE->value => [TaskStatusEnum::PENDING->value,
            TaskStatusEnum::IN_PROGRESS->value, TaskStatusEnum::COMPLETED->value],
            default => TaskStatusEnum::PENDING->value,
        };
        $this->merge([
            'oldExpectedStatus' => $oldExpectedStatus,
        ]);
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $task = $this->route('task');
            $newStatus = $this->status;

            $result = $this->checkPreviousStatus(model: $task, newStatus: $newStatus, oldStatus: $this->oldExpectedStatus);

            if (is_array($result) && $result['key'] === 'fail') {
                $validator->errors()->add('status', $result['msg']);
            }

            if (is_string($result) && $result === 'model not found') {
                $validator->errors()->add('status', 'Task not found.');
            }
        });
    }
}
