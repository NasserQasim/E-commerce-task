<?php

namespace App\Repositories\Eloquent;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderRepository implements OrderRepositoryInterface
{
    public function create(array $data): Order
    {
        return Order::create($data);
    }

    public function createItems(Order $order, array $items): void
    {
        $order->items()->createMany($items);
    }

    public function updateStatus(Order $order, string $status): void
    {
        $order->update(['status' => $status]);
    }

    public function loadItems(Order $order): Order
    {
        return $order->load('items.product');
    }

    public function paginateWithItems(int $perPage): LengthAwarePaginator
    {
        return Order::with('items.product')
            ->latest()
            ->paginate($perPage);
    }
}
