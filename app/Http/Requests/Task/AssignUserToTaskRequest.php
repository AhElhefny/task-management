<?php

namespace App\Http\Requests\Task;

use App\Enums\{TaskStatusEnum, UserRoleEnum};
use App\Traits\HelperTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignUserToTaskRequest extends FormRequest
{
    use HelperTrait;

    protected $stopOnFirstFailure = true;

    public function authorize()
    {
        return $this->user() && $this->user()->isManager() && $this->user()->tokenCan('task:assign');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => [
                'required', 'integer', Rule::exists('users', 'id')
                    ->whereNull('deleted_at')->where('role', UserRoleEnum::USER->value)
            ],
        ];
    }


    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $task = $this->route('task');

            if ($task->status->value != TaskStatusEnum::PENDING->value) {
                $validator->errors()->add('status', 'Task cannot be assigned to a user when it is not pending');
            }
        });
    }
}
