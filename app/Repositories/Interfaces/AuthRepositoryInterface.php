<?php

namespace App\Repositories\Interfaces;

use App\Models\User;

interface AuthRepositoryInterface
{
    /**
     * Find user by email.
     */
    public function findByEmail(string $email): ?User;

    /**
     * Create a new user.
     */
    public function create(array $data): User;

    /**
     * Update user password.
     */
    public function updatePassword(User $user, string $password): bool;

    /**
     * Delete all user tokens.
     */
    public function deleteAllTokens(User $user): int;
}

