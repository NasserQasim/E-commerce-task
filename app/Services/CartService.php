<?php

namespace App\Services;

use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Support\Facades\Redis;

class CartService
{
    private const string CART_PREFIX = 'cart:';
    private const int CART_TTL = 86400; // 24 hours

    public function __construct(
        private ProductRepositoryInterface $productRepository,
    ) {}

    private function cartKey(string $sessionId): string
    {
        return self::CART_PREFIX . $sessionId;
    }

    public function getItems(string $sessionId): array
    {
        $cart = Redis::hgetall($this->cartKey($sessionId));

        if (empty($cart)) {
            return [];
        }

        $items = [];
        foreach ($cart as $productId => $quantity) {
            $product = $this->productRepository->find($productId);
            if ($product) {
                $items[] = [
                    'product' => $product,
                    'quantity' => (int) $quantity,
                    'subtotal' => $product->price * (int) $quantity,
                ];
            }
        }

        return $items;
    }

    public function addItem(string $sessionId, int $productId, int $quantity): array
    {
        $product = $this->productRepository->findOrFail($productId);

        $currentQuantity = (int) Redis::hget($this->cartKey($sessionId), $productId);
        $newQuantity = $currentQuantity + $quantity;

        if (!$product->hasStock($newQuantity)) {
            return [
                'success' => false,
                'message' => "Insufficient stock. Only {$product->stock_quantity} available.",
            ];
        }

        Redis::hset($this->cartKey($sessionId), $productId, $newQuantity);
        Redis::expire($this->cartKey($sessionId), self::CART_TTL);

        return [
            'success' => true,
            'message' => "{$product->name} added to cart.",
        ];
    }

    public function updateItem(string $sessionId, int $productId, int $quantity): array
    {
        if ($quantity <= 0) {
            return $this->removeItem($sessionId, $productId);
        }

        /**
         * @var \App\Models\Product $product
         */
        $product = $this->productRepository->findOrFail($productId);

        if (!$product->hasStock($quantity)) {
            return [
                'success' => false,
                'message' => "Insufficient stock. Only {$product->stock_quantity} available.",
            ];
        }

        Redis::hset($this->cartKey($sessionId), $productId, $quantity);
        Redis::expire($this->cartKey($sessionId), self::CART_TTL);

        return [
            'success' => true,
            'message' => 'Cart updated.',
        ];
    }

    public function removeItem(string $sessionId, int $productId): array
    {
        Redis::hdel($this->cartKey($sessionId), $productId);

        return [
            'success' => true,
            'message' => 'Item removed from cart.',
        ];
    }

    public function clear(string $sessionId): void
    {
        Redis::del($this->cartKey($sessionId));
    }

    public function getTotal(string $sessionId): float
    {
        $items = $this->getItems($sessionId);

        return array_sum(array_column($items, 'subtotal'));
    }

    public function getRawCart(string $sessionId): array
    {
        $cart = Redis::hgetall($this->cartKey($sessionId));

        $items = [];
        foreach ($cart as $productId => $quantity) {
            $items[(int) $productId] = (int) $quantity;
        }

        return $items;
    }

    public function itemCount(string $sessionId): int
    {
        return Redis::hlen($this->cartKey($sessionId));
    }
}
