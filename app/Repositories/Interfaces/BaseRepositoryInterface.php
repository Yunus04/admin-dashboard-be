<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryInterface
{
    /**
     * Get all records.
     */
    public function all(): Collection;

    /**
     * Find record by ID.
     */
    public function find(int $id): ?Model;

    /**
     * Find record by ID or throw exception.
     */
    public function findOrFail(int $id): Model;

    /**
     * Create new record.
     */
    public function create(array $data): Model;

    /**
     * Update record.
     */
    public function update(int $id, array $data): Model;

    /**
     * Delete record (soft delete).
     */
    public function delete(int $id): bool;

    /**
     * Restore soft deleted record.
     */
    public function restore(int $id): bool;

    /**
     * Get with pagination.
     */
    public function paginate(int $perPage = 10);

    /**
     * Find by specific column.
     */
    public function findBy(string $column, mixed $value): ?Model;
}

