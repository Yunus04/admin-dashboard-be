<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find user by email.
     */
    public function findByEmail(string $email): ?User;

    /**
     * Get users by role.
     */
    public function getByRole(string $role): Collection;

    /**
     * Get users with trashed.
     */
    public function getWithTrashed(): Collection;

    /**
     * Find deleted user.
     */
    public function findDeleted(int|string $id): ?User;

    /**
     * Get users count by role.
     */
    public function countByRole(string $role): int;

    /**
     * Get filtered users with pagination, sorting, and search.
     *
     * @param array $filters ['search' => string, 'sort_by' => string, 'sort_order' => string, 'per_page' => int]
     * @return LengthAwarePaginator
     */
    public function getFilteredUsers(array $filters): LengthAwarePaginator;

    /**
     * Get recent users with limit.
     */
    public function getRecentUsers(int $limit = 5): Collection;

    /**
     * Get users count grouped by role.
     */
    public function getUsersByRoleCount(): array;

    /**
     * Get users with merchant role who don't have a merchant profile yet.
     * Used for selecting owner when creating merchant.
     */
    public function getMerchantUsers(): SupportCollection;
}
