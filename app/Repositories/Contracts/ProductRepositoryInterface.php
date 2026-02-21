<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface
{
    public function find(int $id): ?Product;

    public function findOrFail(int $id): Product;

    public function findWithLock(int $id): ?Product;

    public function all(): Collection;

    public function decrementStock(int $id, int $quantity): void;

    public function incrementStock(int $id, int $quantity): void;

    /**
     * @param array<int, int> $items Map of product ID => quantity
     */
    public function bulkIncrementStock(array $items): void;
}
