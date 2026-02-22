<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface
{
    public function find(int $id): ?Product;

    public function findOrFail(int $id): Product;

    /**
     * @param  array<int, int>  $ids
     * @return Collection<int, Product> keyed by product ID
     */
    public function findByIds(array $ids): Collection;

    public function findWithLock(int $id): ?Product;

    /**
     * @param  array<int, int>  $ids
     * @return Collection<int, Product> keyed by product ID, locked for update
     */
    public function findWithLockByIds(array $ids): Collection;

    public function all(): Collection;

    public function decrementStock(int $id, int $quantity): void;

    public function incrementStock(int $id, int $quantity): void;

    /**
     * @param array<int, int> $items Map of product ID => quantity
     */
    public function bulkIncrementStock(array $items): void;

    /**
     * @param array<int, int> $items Map of product ID => quantity
     */
    public function bulkDecrementStock(array $items): void;
}
