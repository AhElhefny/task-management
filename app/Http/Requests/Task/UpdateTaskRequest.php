<?php

namespace App\Http\Requests\Task;

use App\Enums\UserRoleEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only managers can create or update tasks
        return $this->user() && $this->user()->isManager() && $this->user()->tokenCan('task:update');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'min:5', 'max:255'],
            'description' => ['required', 'string', 'max:1000'],
            'due_date'    => ['required', 'date_format:Y-m-d H:i', 'after:now'],
            'user_id'     => [
                'required', 'integer', Rule::exists('users', 'id')
                    ->whereNull('deleted_at')->where('role', UserRoleEnum::USER->value)
            ],
        ];
    }
}
