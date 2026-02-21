<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\RefundService;

class OrderController extends Controller
{
    public function __construct(
        private RefundService $refundService,
    ) {}

    public function index()
    {
        $orders = Order::with('items.product')
            ->latest()
            ->paginate(15);

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load('items.product');

        return view('admin.orders.show', compact('order'));
    }

    public function refund(Order $order)
    {
        $result = $this->refundService->refund($order);

        return redirect()->route('admin.orders.index')->with(
            $result['success'] ? 'success' : 'error',
            $result['message'],
        );
    }
}
