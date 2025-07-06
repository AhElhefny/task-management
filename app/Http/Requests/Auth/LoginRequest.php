<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoginRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    protected $stopOnFirstFailure = true;
    public function rules(): array
    {
        return [
            'email'    => [
                'required', 'email:rfc,dns',
                Rule::exists('users', 'email')->whereNull('deleted_at')
            ],
            'password' => ['required', 'string', 'min:6', 'max:100'],
        ];
    }
}
