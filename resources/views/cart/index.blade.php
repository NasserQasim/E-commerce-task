@extends('layouts.app')

@section('title', 'Cart - ShopFlow')

@section('content')
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Shopping Cart</h1>
    </div>

    @if(count($items) > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="text-left px-6 py-4 text-sm font-medium text-gray-500">Product</th>
                        <th class="text-center px-6 py-4 text-sm font-medium text-gray-500">Price</th>
                        <th class="text-center px-6 py-4 text-sm font-medium text-gray-500">Quantity</th>
                        <th class="text-center px-6 py-4 text-sm font-medium text-gray-500">Subtotal</th>
                        <th class="text-center px-6 py-4 text-sm font-medium text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($items as $item)
                        <tr>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $item['product']->name }}</td>
                            <td class="px-6 py-4 text-center text-gray-600">${{ number_format($item['product']->price, 2) }}</td>
                            <td class="px-6 py-4 text-center">
                                <form action="{{ route('cart.update', $item['product']->id) }}" method="POST" class="inline-flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="quantity" onchange="this.form.submit()" class="border border-gray-200 rounded-lg px-2 py-1 text-sm focus:ring-2 focus:ring-indigo-500">
                                        @for($i = 1; $i <= min($item['product']->stock_quantity, 10); $i++)
                                            <option value="{{ $i }}" {{ $item['quantity'] == $i ? 'selected' : '' }}>{{ $i }}</option>
                                        @endfor
                                    </select>
                                </form>
                            </td>
                            <td class="px-6 py-4 text-center font-semibold text-gray-900">${{ number_format($item['subtotal'], 2) }}</td>
                            <td class="px-6 py-4 text-center">
                                <form action="{{ route('cart.remove', $item['product']->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-sm font-medium transition">
                                        Remove
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="bg-gray-50 px-6 py-5 border-t flex items-center justify-between">
                <div>
                    <span class="text-gray-500">Total:</span>
                    <span class="text-2xl font-bold text-gray-900 ml-2">${{ number_format($total, 2) }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('products.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                        Continue Shopping
                    </a>
                    <form action="{{ route('checkout.process') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                            Checkout (COD)
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-20">
            <svg class="w-20 h-20 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
            </svg>
            <p class="text-gray-400 text-lg mb-4">Your cart is empty.</p>
            <a href="{{ route('products.index') }}" class="inline-block px-6 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                Browse Products
            </a>
        </div>
    @endif
@endsection
