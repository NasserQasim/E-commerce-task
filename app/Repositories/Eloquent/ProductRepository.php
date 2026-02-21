<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ProductRepository implements ProductRepositoryInterface
{
    public function find(int $id): ?Product
    {
        return Product::find($id);
    }

    public function findOrFail(int $id): Product
    {
        return Product::findOrFail($id);
    }

    public function findWithLock(int $id): ?Product
    {
        return Product::lockForUpdate()->find($id);
    }

    public function all(): Collection
    {
        return Product::all();
    }

    public function decrementStock(int $id, int $quantity): void
    {
        Product::where('id', $id)->decrement('stock_quantity', $quantity);
    }

    public function incrementStock(int $id, int $quantity): void
    {
        Product::where('id', $id)->increment('stock_quantity', $quantity);
    }

    public function bulkIncrementStock(array $items): void
    {
        if (empty($items)) {
            return;
        }

        $cases = [];
        $bindings = [];
        foreach ($items as $id => $quantity) {
            $cases[] = 'WHEN id = ? THEN stock_quantity + ?';
            $bindings[] = $id;
            $bindings[] = $quantity;
        }

        $ids = array_keys($items);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $bindings = array_merge($bindings, $ids);

        DB::update(
            'UPDATE products SET stock_quantity = CASE ' . implode(' ', $cases) . ' END WHERE id IN (' . $placeholders . ')',
            $bindings
        );
    }
}
