<?php

namespace App\Factories;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\ValueObjects\Money;

class OrderFactory
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly ProductRepositoryInterface $productRepository,
    ) {}

    /**
     * Create an order from validated cart items.
     *
     * Must be called inside a DB transaction.
     *
     * @param  array<int, array{product: \App\Models\Product, quantity: int}>  $validatedItems
     */
    public function createFromCart(array $validatedItems): Order
    {
        $total = Money::fromCents(0);
        $orderItems = [];

        foreach ($validatedItems as ['product' => $product, 'quantity' => $quantity]) {
            $linePrice = Money::fromDecimal($product->price)->multiply($quantity);
            $total = $total->add($linePrice);

            $orderItems[] = [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price_at_purchase' => $product->price,
            ];

            $this->productRepository->decrementStock($product->id, $quantity);
        }

        $order = $this->orderRepository->create([
            'total_amount' => $total->toDecimal(),
            'status' => 'completed',
        ]);

        $this->orderRepository->createItems($order, $orderItems);

        return $order;
    }
}
