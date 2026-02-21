@extends('layouts.app')

@section('title', "Order #$order->id - Admin")

@section('content')
    <div class="mb-8 flex items-center justify-between">
        <div>
            <a href="{{ route('admin.orders.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">&larr; Back to Orders</a>
            <h1 class="text-3xl font-bold text-gray-900 mt-2">Order #{{ $order->id }}</h1>
        </div>
        <div>
            @if($order->status === 'completed')
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">Completed</span>
            @elseif($order->status === 'pending')
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">Pending</span>
            @elseif($order->status === 'refunded')
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">Refunded</span>
            @endif
        </div>
    </div>

    {{-- Order Summary --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <p class="text-sm text-gray-500">Total Amount</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">${{ number_format($order->total_amount, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <p class="text-sm text-gray-500">Items</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $order->items->count() }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <p class="text-sm text-gray-500">Placed At</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $order->created_at->format('M d, Y') }}</p>
        </div>
    </div>

    {{-- Order Items --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50">
            <h2 class="font-semibold text-gray-900">Order Items</h2>
        </div>
        <table class="w-full">
            <thead class="border-b">
                <tr>
                    <th class="text-left px-6 py-3 text-sm font-medium text-gray-500">Product</th>
                    <th class="text-center px-6 py-3 text-sm font-medium text-gray-500">Price at Purchase</th>
                    <th class="text-center px-6 py-3 text-sm font-medium text-gray-500">Quantity</th>
                    <th class="text-right px-6 py-3 text-sm font-medium text-gray-500">Subtotal</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($order->items as $item)
                    <tr>
                        <td class="px-6 py-4 font-medium text-gray-900">
                            {{ $item->product->name ?? 'Deleted Product' }}
                        </td>
                        <td class="px-6 py-4 text-center text-gray-600">${{ number_format($item->price_at_purchase, 2) }}</td>
                        <td class="px-6 py-4 text-center text-gray-600">{{ $item->quantity }}</td>
                        <td class="px-6 py-4 text-right font-semibold text-gray-900">${{ number_format($item->price_at_purchase * $item->quantity, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Refund Action --}}
    @if($order->isRefundable())
        <div class="mt-6 flex justify-end">
            <form action="{{ route('admin.orders.refund', $order) }}" method="POST" onsubmit="return confirm('Are you sure you want to refund this order? Stock will be restored.')">
                @csrf
                <input type="hidden" name="idempotency_key" value="{{ Str::uuid() }}">
                <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition">
                    Refund Order
                </button>
            </form>
        </div>
    @endif
@endsection
