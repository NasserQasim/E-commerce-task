<?php

namespace App\Services;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RefundService
{
    private const string IDEMPOTENCY_PREFIX = 'refund_idempotency:';
    private const int IDEMPOTENCY_TTL = 86400; // 24 hours

    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private OrderRepositoryInterface $orderRepository,
    ) {}

    public function refund(Order $order, string $idempotencyKey): array
    {
        $cacheKey = self::IDEMPOTENCY_PREFIX . $idempotencyKey;

        // Return cached response if this key was already processed
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        if (!$order->isRefundable()) {
            $result = [
                'success' => false,
                'message' => 'This order cannot be refunded. Only completed orders are eligible.',
            ];

            Cache::put($cacheKey, $result, self::IDEMPOTENCY_TTL);

            return $result;
        }

        try {
            DB::transaction(function () use ($order) {
                $this->orderRepository->loadItems($order);

                foreach ($order->items as $item) {
                    $this->productRepository->incrementStock($item->product_id, $item->quantity);
                }

                $this->orderRepository->updateStatus($order, 'refunded');
            });

            $result = [
                'success' => true,
                'message' => "Order #{$order->id} has been refunded and stock restored.",
            ];
        } catch (\Exception $e) {
            $result = [
                'success' => false,
                'message' => 'Refund failed: ' . $e->getMessage(),
            ];
        }

        Cache::put($cacheKey, $result, self::IDEMPOTENCY_TTL);

        return $result;
    }
}
