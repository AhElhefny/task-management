<?php

namespace App\Http\Controllers;

use App\Enums\UserRoleEnum;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\{AuthService, BaseService};
use App\Traits\HelperTrait;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    use HelperTrait;

    private $userService, $authService;

    public function __construct()
    {
        $this->userService = new BaseService(User::class);
        $this->authService = new AuthService(User::class);
    }

    public function login(LoginRequest $request)
    {
        $user = $this->userService->first(conditions: [
            'email' => $request->email
        ]);
        if (!$this->authService->checkPasswordValidation($user, $request->password)) {
            return $this->apiResponse(msg: 'Invalid credentials', code: Response::HTTP_UNAUTHORIZED);
        }
        $token = $this->authService->login($user);
        return $this->apiResponse(data: [
            'token' => $token,
            'type'  => UserRoleEnum::MANAGER->value == $user->role->value ? 'manager' : 'user',
            'user'  => UserResource::make($user)
        ]);
    }

    public function logout()
    {
        $this->authService->logout();
        return $this->apiResponse(msg: 'Logout successfully');
    }
}
