<?php

namespace Tests\Feature;

use App\Actions\ProcessCheckoutAction;
use App\Actions\RefundOrderAction;
use App\Models\Order;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Redis::flushdb();
    }

    public function test_checkout_creates_order_and_reduces_stock(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'price' => 25.00,
            'stock_quantity' => 10,
        ]);

        $cartService = app(CartService::class);
        $sessionId = 'test-session';

        $cartService->addItem($sessionId, $product->id, 3);

        $checkoutAction = app(ProcessCheckoutAction::class);
        $result = $checkoutAction->execute($sessionId);

        $this->assertTrue($result->success);
        $this->assertDatabaseHas('orders', [
            'total_amount' => 75.00,
            'status' => 'completed',
        ]);

        $product->refresh();
        $this->assertEquals(7, $product->stock_quantity);
    }

    public function test_checkout_fails_with_empty_cart(): void
    {
        $checkoutAction = app(ProcessCheckoutAction::class);
        $result = $checkoutAction->execute('empty-session');

        $this->assertFalse($result->success);
        $this->assertEquals('Your cart is empty.', $result->message);
    }

    public function test_checkout_fails_when_stock_insufficient(): void
    {
        $product = Product::create([
            'name' => 'Low Stock Product',
            'price' => 10.00,
            'stock_quantity' => 2,
        ]);

        $cartService = app(CartService::class);
        $sessionId = 'test-stock-session';

        // Manually set quantity higher than stock via Redis
        Redis::hset("cart:{$sessionId}", $product->id, 5);

        $checkoutAction = app(ProcessCheckoutAction::class);
        $result = $checkoutAction->execute($sessionId);

        $this->assertFalse($result->success);
        $this->assertStringContainsString('Insufficient stock', $result->message);
    }

    public function test_refund_restores_stock_and_updates_status(): void
    {
        $product = Product::create([
            'name' => 'Refund Test Product',
            'price' => 50.00,
            'stock_quantity' => 5,
        ]);

        $order = Order::create([
            'total_amount' => 100.00,
            'status' => 'completed',
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'price_at_purchase' => 50.00,
        ]);

        $this->post(route('admin.orders.refund', $order), [
            'idempotency_key' => 'test-refund-key-1',
        ]);

        $order->refresh();
        $product->refresh();

        $this->assertEquals('refunded', $order->status);
        $this->assertEquals(7, $product->stock_quantity);
    }

    public function test_double_refund_is_prevented(): void
    {
        $order = Order::create([
            'total_amount' => 50.00,
            'status' => 'refunded',
        ]);

        $response = $this->post(route('admin.orders.refund', $order), [
            'idempotency_key' => 'test-refund-key-2',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_idempotency_key_returns_same_response(): void
    {
        $product = Product::create([
            'name' => 'Idempotency Product',
            'price' => 30.00,
            'stock_quantity' => 10,
        ]);

        $order = Order::create([
            'total_amount' => 60.00,
            'status' => 'completed',
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'price_at_purchase' => 30.00,
        ]);

        $refundAction = app(RefundOrderAction::class);
        $key = 'idempotent-test-key';

        $firstResult = $refundAction->execute($order, $key);
        $secondResult = $refundAction->execute($order, $key);

        $this->assertEquals($firstResult, $secondResult);
        $this->assertTrue($firstResult->success);

        // Stock should only be restored once
        $product->refresh();
        $this->assertEquals(12, $product->stock_quantity);
    }
}
