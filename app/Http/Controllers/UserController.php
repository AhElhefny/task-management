<?php

namespace App\Http\Controllers;

use App\Enums\UserRoleEnum;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\BaseService;
use App\Traits\HelperTrait;

class UserController extends Controller
{
    use HelperTrait;

    protected $userService;

    public function __construct()
    {
        $this->userService = new BaseService(User::class);
    }

    public function __invoke()
    {
        $users = $this->userService->get(
            conditions: ['role' => UserRoleEnum::USER->value]
        );
        return $this->apiResponse(
            msg: 'Users retrieved successfully',
            data: UserResource::collection($users),
        );
    }
}
