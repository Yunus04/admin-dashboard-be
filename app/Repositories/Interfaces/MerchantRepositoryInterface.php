<?php

namespace App\Repositories\Interfaces;

use App\Models\Merchant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface MerchantRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get merchant by user ID.
     */
    public function findByUserId(int $userId): ?Merchant;

    /**
     * Get merchants by user ID (single merchant per user).
     */
    public function getByUserId(int $userId): Collection;

    /**
     * Get merchants by user IDs.
     */
    public function getByUserIds(array $userIds): Collection;

    /**
     * Get merchant with user relation.
     */
    public function findWithUser(int $id): ?Merchant;

    /**
     * Search merchants by business name.
     */
    public function searchByBusinessName(string $query): Collection;

    /**
     * Count active merchants.
     */
    public function countActive(): int;

    /**
     * Get filtered merchants with pagination, sorting, and search.
     *
     * @param array $filters ['search' => string, 'sort_by' => string, 'sort_order' => string, 'per_page' => int, 'user_id' => int|null]
     * @return LengthAwarePaginator
     */
    public function getFilteredMerchants(array $filters): LengthAwarePaginator;

    /**
     * Count inactive merchants (soft deleted).
     */
    public function countInactive(): int;

    /**
     * Get recent merchants with user relation.
     */
    public function getRecentWithUser(int $limit = 5): Collection;

    /**
     * Get merchants with active users only.
     */
    public function getWithActiveUser(): Collection;
}

