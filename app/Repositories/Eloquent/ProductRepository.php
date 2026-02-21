<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

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
}
