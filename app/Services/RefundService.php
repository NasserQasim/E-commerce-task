<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class RefundService
{
    public function refund(Order $order): array
    {
        if (!$order->isRefundable()) {
            return [
                'success' => false,
                'message' => 'This order cannot be refunded. Only completed orders are eligible.',
            ];
        }

        try {
            DB::transaction(function () use ($order) {
                $order->load('items');

                foreach ($order->items as $item) {
                    Product::where('id', $item->product_id)
                        ->increment('stock_quantity', $item->quantity);
                }

                $order->update(['status' => 'refunded']);
            });

            return [
                'success' => true,
                'message' => "Order #{$order->id} has been refunded and stock restored.",
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Refund failed: ' . $e->getMessage(),
            ];
        }
    }
}
