<?php

namespace App\Repositories\Contracts;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface
{
    public function create(array $data): Order;

    public function createItems(Order $order, array $items): void;

    public function updateStatus(Order $order, string $status): void;

    public function loadItems(Order $order): Order;

    public function paginateWithItems(int $perPage): LengthAwarePaginator;
}
