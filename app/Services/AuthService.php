<?php

namespace App\Services;

use App\Enums\UserRoleEnum;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * Create a new class instance.
     */
    protected $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function login($user)
    {
        try {
            return $this->createToken($user);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function checkPasswordValidation($user, $password): bool
    {
        return Hash::check($password, $user->password);
    }

    protected function createToken($user)
    {
        try {
            return $user->createToken('user-token', $this->getUserAbilities($user))->plainTextToken;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    protected function getUserAbilities($user)
    {
        return $user->role->value == UserRoleEnum::USER->value ?
            ['task:index', 'task:update-status'] :
            ['task:create', 'task:update', 'task:index', 'task:show', 'task:assign', 'user:index'];
    }

    public function logout()
    {
        try {
            return auth()->user()->tokens()->delete();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
