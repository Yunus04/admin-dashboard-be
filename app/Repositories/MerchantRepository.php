<?php

namespace App\Repositories;

use App\Models\Merchant;
use App\Repositories\Interfaces\MerchantRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MerchantRepository extends BaseRepository implements MerchantRepositoryInterface
{
    protected $model;

    public function __construct(Merchant $model)
    {
        parent::__construct($model);
        $this->model = $model;
    }

    /**
     * Get merchant by user ID.
     */
    public function findByUserId(int $userId): ?Merchant
    {
        return $this->model->where('user_id', $userId)->first();
    }

    /**
     * Get merchants by user ID (single merchant per user).
     */
    public function getByUserId(int $userId): Collection
    {
        return $this->model->where('user_id', $userId)->get();
    }

    /**
     * Get merchants by user IDs.
     */
    public function getByUserIds(array $userIds): Collection
    {
        return $this->model->whereIn('user_id', $userIds)->get();
    }

    /**
     * Get merchant with user relation.
     */
    public function findWithUser(int $id): ?Merchant
    {
        return $this->model->with('user')->find($id);
    }

    /**
     * Search merchants by business name (case-insensitive).
     */
    public function searchByBusinessName(string $query): Collection
    {
        return $this->model->whereRaw('LOWER(business_name) LIKE ?', ['%' . strtolower($query) . '%'])->get();
    }

    /**
     * Count active merchants.
     */
    public function countActive(): int
    {
        return $this->model->count();
    }

    /**
     * Get merchant statistics for dashboard.
     */
    public function getStatistics(): array
    {
        return [
            'total' => $this->model->count(),
            'active' => $this->countActive(),
        ];
    }

    /**
     * Get merchants with user data.
     */
    public function getWithUser(): Collection
    {
        return $this->model->with('user')->get();
    }

    /**
     * Get recent merchants.
     */
    public function getRecent(int $limit = 5): Collection
    {
        return $this->model->with('user')->latest()->limit($limit)->get();
    }

    /**
     * Get filtered merchants with pagination, sorting, and search.
     */
    public function getFilteredMerchants(array $filters): LengthAwarePaginator
    {
        $perPage = min((int) ($filters['per_page'] ?? 10), 100);
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $search = $filters['search'] ?? null;
        $userId = $filters['user_id'] ?? null;

        $query = $this->model->withTrashed()->with('user');

        // Filter by user_id if provided (for merchant role)
        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        // Apply search filter (case-insensitive)
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(business_name) LIKE ?', ['%' . strtolower($search) . '%'])
                    ->orWhereRaw('LOWER(address) LIKE ?', ['%' . strtolower($search) . '%']);
            });
        }

        // Apply sorting
        $allowedSortFields = ['id', 'user_id', 'business_name', 'phone', 'created_at'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        return $query->paginate($perPage);
    }

    /**
     * Count inactive merchants (soft deleted).
     */
    public function countInactive(): int
    {
        return $this->model->whereHas('user', function ($query) {
            $query->whereNotNull('deleted_at');
        })->count();
    }

    /**
     * Get recent merchants with user relation.
     */
    public function getRecentWithUser(int $limit = 5): Collection
    {
        return $this->model->with('user')
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get merchants with active users only.
     */
    public function getWithActiveUser(): Collection
    {
        return $this->model->whereHas('user', function ($query) {
            $query->whereNull('deleted_at');
        })->get();
    }
}

