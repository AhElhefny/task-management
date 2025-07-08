<?php

namespace App\Http\Requests;

use App\Enums\UserRoleEnum;
use App\Enums\TaskStatusEnum;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use App\Rules\CheckIfItRepeatedDependency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class AddTaskDependencyRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only managers can add task dependencies
        return $this->user() && $this->user()->isManager() && $this->user()->tokenCan('task:add-dependencies');
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
            'dependency_ids' => [
                'required',
                'array',
                'min:1',
            ],
            'dependency_ids.*' => [
                'required',
                'integer',
                Rule::exists('tasks', 'id')
                    ->where(function ($query) {
                        $query->where('status', '!=', TaskStatusEnum::CANCELLED->value);
                    }),
                new CheckIfItRepeatedDependency($taskId),
            ],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'dependency_ids.*.required' => 'Dependency #:position is required.',
            'dependency_ids.*.integer' => 'Dependency #:position must be an integer.',
            'dependency_ids.*.exists' => 'Dependency #:position is invalid or does not exist.',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * This will add a :position replacement for each dependency item.
     */
    protected function formatValidationErrors(Validator $validator)
    {
        $messages = $validator->errors()->getMessages();
        $formatted = [];
        foreach ($messages as $key => $errors) {
            if (preg_match('/dependency_ids\\.(\\d+)/', $key, $matches)) {
                $index = (int)$matches[1] + 1;
                foreach ($errors as $error) {
                    $formatted[] = str_replace(':position', $index, $error);
                }
            } else {
                foreach ($errors as $error) {
                    $formatted[] = $error;
                }
            }
        }
        return $formatted;
    }
}
