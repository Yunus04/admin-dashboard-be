<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\AuthRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class AuthRepository implements AuthRepositoryInterface
{
    protected $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    /**
     * Find user by email.
     */
    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Create a new user.
     */
    public function create(array $data): User
    {
        return $this->model->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'] ?? 'merchant',
        ]);
    }

    /**
     * Update user password.
     */
    public function updatePassword(User $user, string $password): bool
    {
        return $user->update([
            'password' => Hash::make($password),
        ]);
    }

    /**
     * Delete all user tokens.
     */
    public function deleteAllTokens(User $user): int
    {
        return $user->tokens()->delete();
    }
}

