<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    protected $model;

    public function __construct(User $model)
    {
        parent::__construct($model);
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
     * Get users by role.
     */
    public function getByRole(string $role): Collection
    {
        return $this->model->where('role', $role)->get();
    }

    /**
     * Get users with trashed.
     */
    public function getWithTrashed(): Collection
    {
        return $this->model->withTrashed()->get();
    }

    /**
     * Find deleted user.
     */
    public function findDeleted(int|string $id): ?User
    {
        return $this->model->withTrashed()->find($id);
    }

    /**
     * Get users count by role.
     */
    public function countByRole(string $role): int
    {
        return $this->model->where('role', $role)->count();
    }

    /**
     * Get user statistics for dashboard.
     */
    public function getStatistics(): array
    {
        return [
            'total' => $this->model->count(),
            'by_role' => [
                'super_admin' => $this->countByRole('super_admin'),
                'admin' => $this->countByRole('admin'),
                'merchant' => $this->countByRole('merchant'),
            ],
        ];
    }

    /**
     * Get filtered users with pagination, sorting, and search.
     * Only returns non-deleted users by default.
     */
    public function getFilteredUsers(array $filters): LengthAwarePaginator
    {
        $perPage = min((int) ($filters['per_page'] ?? 10), 100);
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $search = $filters['search'] ?? null;
        $includeDeleted = $filters['include_deleted'] ?? false;

        // Include trashed only if explicitly requested
        $query = $includeDeleted ? $this->model->withTrashed() : $this->model->whereNull('deleted_at');

        // Eager load merchant relationship
        $query->with('merchant');

        // Apply search filter (case-insensitive)
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%'])
                    ->orWhereRaw('LOWER(email) LIKE ?', ['%' . strtolower($search) . '%']);
            });
        }

        // Apply sorting
        $allowedSortFields = ['id', 'name', 'email', 'role', 'created_at'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get recent users with limit.
     */
    public function getRecentUsers(int $limit = 5): Collection
    {
        return new Collection($this->model->latest()
            ->limit($limit)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'created_at' => $user->created_at,
                ];
            }));
    }

    /**
     * Get users count grouped by role.
     */
    public function getUsersByRoleCount(): array
    {
        return $this->model->select('role')
            ->selectRaw('count(*) as count')
            ->groupBy('role')
            ->get()
            ->pluck('count', 'role')
            ->toArray();
    }

    /**
     * Get users with merchant role who don't have a merchant profile yet.
     * Used for selecting owner when creating merchant.
     */
    public function getMerchantUsers(): SupportCollection
    {
        return new SupportCollection(
            $this->model->where('role', 'merchant')
                ->whereDoesntHave('merchant')
                ->orderBy('name')
                ->get()
        );
    }
}

