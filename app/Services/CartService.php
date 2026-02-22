<?php

namespace App\Services;

use App\DTOs\CartItem;
use App\DTOs\ServiceResult;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Support\Facades\Redis;

class CartService
{
    private const string CART_PREFIX = 'cart:';
    private const int CART_TTL = 86400; // 24 hours

    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
    ) {}

    private function cartKey(string $sessionId): string
    {
        return self::CART_PREFIX . $sessionId;
    }

    /**
     * @return CartItem[]
     */
    public function getItems(string $sessionId): array
    {
        $cart = Redis::hgetall($this->cartKey($sessionId));

        if (empty($cart)) {
            return [];
        }

        $productIds = array_map('intval', array_keys($cart));
        $products = $this->productRepository->findByIds($productIds);

        $items = [];
        foreach ($cart as $productId => $quantity) {
            $product = $products->get((int) $productId);
            if ($product) {
                $items[] = new CartItem($product, (int) $quantity);
            }
        }

        return $items;
    }

    public function addItem(string $sessionId, int $productId, int $quantity): ServiceResult
    {
        $product = $this->productRepository->findOrFail($productId);

        $currentQuantity = (int) Redis::hget($this->cartKey($sessionId), $productId);
        $newQuantity = $currentQuantity + $quantity;

        if (!$product->hasStock($newQuantity)) {
            return ServiceResult::failure(
                "Insufficient stock. Only {$product->stock_quantity} available."
            );
        }

        Redis::hset($this->cartKey($sessionId), $productId, $newQuantity);
        Redis::expire($this->cartKey($sessionId), self::CART_TTL);

        return ServiceResult::success("{$product->name} added to cart.");
    }

    public function updateItem(string $sessionId, int $productId, int $quantity): ServiceResult
    {
        if ($quantity <= 0) {
            return $this->removeItem($sessionId, $productId);
        }

        $product = $this->productRepository->findOrFail($productId);

        if (!$product->hasStock($quantity)) {
            return ServiceResult::failure(
                "Insufficient stock. Only {$product->stock_quantity} available."
            );
        }

        Redis::hset($this->cartKey($sessionId), $productId, $quantity);
        Redis::expire($this->cartKey($sessionId), self::CART_TTL);

        return ServiceResult::success('Cart updated.');
    }

    public function removeItem(string $sessionId, int $productId): ServiceResult
    {
        Redis::hdel($this->cartKey($sessionId), $productId);

        return ServiceResult::success('Item removed from cart.');
    }

    public function clear(string $sessionId): void
    {
        Redis::del($this->cartKey($sessionId));
    }

    /**
     * @param  CartItem[]|null  $items  Pre-fetched items to avoid re-querying
     */
    public function getTotal(string $sessionId, ?array $items = null): float
    {
        $items ??= $this->getItems($sessionId);

        return array_sum(array_map(fn(CartItem $item) => $item->subtotal->toDecimal(), $items));
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
