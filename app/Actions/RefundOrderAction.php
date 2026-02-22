<?php

namespace App\Actions;

use App\DTOs\ServiceResult;
use App\Events\OrderRefunded;
use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RefundOrderAction
{
    private const string IDEMPOTENCY_PREFIX = 'refund_idempotency:';
    private const int IDEMPOTENCY_TTL = 86400;

    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly OrderRepositoryInterface $orderRepository,
    ) {}

    public function execute(Order $order, string $idempotencyKey): ServiceResult
    {
        $cacheKey = self::IDEMPOTENCY_PREFIX . $idempotencyKey;

        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        if (!$order->isRefundable()) {
            $result = ServiceResult::failure(
                'This order cannot be refunded. Only completed orders are eligible.'
            );
            Cache::put($cacheKey, $result, self::IDEMPOTENCY_TTL);

            return $result;
        }

        try {
            DB::transaction(function () use ($order) {
                $this->orderRepository->loadItems($order);

                $stockMap = [];
                foreach ($order->items as $item) {
                    $stockMap[$item->product_id] = ($stockMap[$item->product_id] ?? 0) + $item->quantity;
                }
                $this->productRepository->bulkIncrementStock($stockMap);

                $this->orderRepository->updateStatus($order, 'refunded');
            });

            $result = ServiceResult::success("Order #{$order->id} has been refunded and stock restored.");

            OrderRefunded::dispatch($order);
        } catch (\Exception $e) {
            $result = ServiceResult::failure('Refund failed: ' . $e->getMessage());
        }

        Cache::put($cacheKey, $result, self::IDEMPOTENCY_TTL);

        return $result;
    }
}
