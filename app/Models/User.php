<?php

namespace App\Models;

use App\Enums\UserRoleEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'role'     => UserRoleEnum::class,
        ];
    }

    /**
     * Get the tasks assigned to the user.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'user_id');
    }

    /**
     * Check if the user is a manager.
     */
    public function isManager(): bool
    {
        return $this->role->value === UserRoleEnum::MANAGER->value;
    }
    public function isNormal(): bool
    {
        return $this->role->value === UserRoleEnum::USER->value;
    }
}
