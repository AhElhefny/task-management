<?php

namespace App\Http\Requests;

use App\Enums\UserRoleEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class AddTaskDependencyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only managers can add task dependencies
        return $this->user() && $this->user()->role === UserRoleEnum::MANAGER;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $taskId = $this->route('task')->id;

        return [
            'dependency_id' => [
                'required',
                'integer',
                'exists:tasks,id',
                // Ensure a task can't depend on itself
                function ($attribute, $value, $fail) use ($taskId) {
                    if ($value == $taskId) {
                        $fail('A task cannot depend on itself.');
                    }
                },
            ],
        ];
    }
}
