@extends('layouts.app')

@section('title', 'Admin - Orders')

@section('content')
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Order Management</h1>
        <p class="mt-2 text-gray-500">View and manage all orders.</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-6 py-4 text-sm font-medium text-gray-500">Order ID</th>
                    <th class="text-center px-6 py-4 text-sm font-medium text-gray-500">Items</th>
                    <th class="text-center px-6 py-4 text-sm font-medium text-gray-500">Total</th>
                    <th class="text-center px-6 py-4 text-sm font-medium text-gray-500">Status</th>
                    <th class="text-center px-6 py-4 text-sm font-medium text-gray-500">Date</th>
                    <th class="text-center px-6 py-4 text-sm font-medium text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($orders as $order)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 font-mono text-sm font-medium text-gray-900">#{{ $order->id }}</td>
                        <td class="px-6 py-4 text-center text-gray-600">{{ $order->items->count() }} items</td>
                        <td class="px-6 py-4 text-center font-semibold text-gray-900">${{ number_format($order->total_amount, 2) }}</td>
                        <td class="px-6 py-4 text-center">
                            @if($order->status === 'completed')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Completed</span>
                            @elseif($order->status === 'pending')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Pending</span>
                            @elseif($order->status === 'refunded')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Refunded</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center text-sm text-gray-500">{{ $order->created_at->format('M d, Y H:i') }}</td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.orders.show', $order) }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium transition">
                                    View
                                </a>
                                @if($order->isRefundable())
                                    <form action="{{ route('admin.orders.refund', $order) }}" method="POST" onsubmit="return confirm('Are you sure you want to refund this order?')">
                                        @csrf
                                        <button type="submit" class="text-red-500 hover:text-red-700 text-sm font-medium transition">
                                            Refund
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center text-gray-400">No orders found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $orders->links() }}
    </div>
@endsection
