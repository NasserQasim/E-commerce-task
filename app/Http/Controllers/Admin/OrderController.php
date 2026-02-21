<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Services\RefundService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private RefundService $refundService,
        private OrderRepositoryInterface $orderRepository,
    ) {}

    public function index()
    {
        $orders = $this->orderRepository->paginateWithItems(15);

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $this->orderRepository->loadItems($order);

        return view('admin.orders.show', compact('order'));
    }

    public function refund(Request $request, Order $order)
    {
        $request->validate([
            'idempotency_key' => 'required|string|max:64',
        ]);

        $result = $this->refundService->refund($order, $request->input('idempotency_key'));

        return redirect()->route('admin.orders.index')->with(
            $result['success'] ? 'success' : 'error',
            $result['message'],
        );
    }
}
