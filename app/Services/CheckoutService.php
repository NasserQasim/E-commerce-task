<?php

namespace App\Services;

use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Support\Facades\DB;

readonly class CheckoutService
{
    public function __construct(
        private CartService                $cartService,
        private ProductRepositoryInterface $productRepository,
        private OrderRepositoryInterface   $orderRepository,
    ) {}

    public function process(string $sessionId): array
    {
        $cartItems = $this->cartService->getRawCart($sessionId);

        if (empty($cartItems)) {
            return [
                'success' => false,
                'message' => 'Your cart is empty.',
            ];
        }

        try {
            $order = DB::transaction(function () use ($cartItems, $sessionId) {
                $totalAmount = 0;
                $orderItems = [];

                foreach ($cartItems as $productId => $quantity) {
                    $product = $this->productRepository->findWithLock($productId);

                    if (!$product) {
                        throw new \RuntimeException("Product #{$productId} no longer exists.");
                    }

                    if (!$product->hasStock($quantity)) {
                        throw new \RuntimeException(
                            "Insufficient stock for {$product->name}. Only {$product->stock_quantity} left."
                        );
                    }

                    $this->productRepository->decrementStock($product->id, $quantity);

                    $lineTotal = $product->price * $quantity;
                    $totalAmount += $lineTotal;

                    $orderItems[] = [
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'price_at_purchase' => $product->price,
                    ];
                }

                $order = $this->orderRepository->create([
                    'total_amount' => $totalAmount,
                    'status' => 'completed',
                ]);

                $this->orderRepository->createItems($order, $orderItems);

                $this->cartService->clear($sessionId);

                return $order;
            });

            return [
                'success' => true,
                'message' => "Order #{$order->id} placed successfully!",
                'order' => $order,
            ];
        } catch (\RuntimeException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
