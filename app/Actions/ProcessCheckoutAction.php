<?php

namespace App\Actions;

use App\DTOs\CheckoutResult;
use App\Events\OrderPlaced;
use App\Factories\OrderFactory;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\CartService;
use Illuminate\Support\Facades\DB;

class ProcessCheckoutAction
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly OrderFactory $orderFactory,
    ) {}

    public function execute(string $sessionId): CheckoutResult
    {
        $cartItems = $this->cartService->getRawCart($sessionId);

        if (empty($cartItems)) {
            return CheckoutResult::failure('Your cart is empty.');
        }

        try {
            $order = DB::transaction(function () use ($cartItems, $sessionId) {
                $products = $this->productRepository->findWithLockByIds(array_keys($cartItems));

                $validatedItems = [];
                foreach ($cartItems as $productId => $quantity) {
                    $product = $products->get($productId);

                    if (!$product) {
                        throw new \RuntimeException("Product #{$productId} no longer exists.");
                    }

                    if (!$product->hasStock($quantity)) {
                        throw new \RuntimeException(
                            "Insufficient stock for {$product->name}. Only {$product->stock_quantity} left."
                        );
                    }

                    $validatedItems[] = ['product' => $product, 'quantity' => $quantity];
                }

                $order = $this->orderFactory->createFromCart($validatedItems);

                $this->cartService->clear($sessionId);

                return $order;
            });

            OrderPlaced::dispatch($order);

            return CheckoutResult::withOrder("Order #{$order->id} placed successfully!", $order);
        } catch (\RuntimeException $e) {
            return CheckoutResult::failure($e->getMessage());
        }
    }
}
