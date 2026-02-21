@extends('layouts.app')

@section('title', 'Checkout - ShopFlow')

@section('content')
    <div class="max-w-3xl mx-auto">
        {{-- Progress Steps --}}
        <div class="flex items-center justify-center mb-10">
            <div class="flex items-center">
                <div class="flex items-center justify-center w-10 h-10 bg-indigo-600 text-white rounded-full text-sm font-bold">1</div>
                <span class="ml-2 text-sm font-medium text-indigo-600">Cart</span>
            </div>
            <div class="w-16 h-0.5 bg-indigo-600 mx-3"></div>
            <div class="flex items-center">
                <div class="flex items-center justify-center w-10 h-10 bg-indigo-600 text-white rounded-full text-sm font-bold">2</div>
                <span class="ml-2 text-sm font-medium text-indigo-600">Review</span>
            </div>
            <div class="w-16 h-0.5 bg-gray-300 mx-3"></div>
            <div class="flex items-center">
                <div class="flex items-center justify-center w-10 h-10 bg-gray-200 text-gray-500 rounded-full text-sm font-bold">3</div>
                <span class="ml-2 text-sm font-medium text-gray-400">Done</span>
            </div>
        </div>

        <h1 class="text-3xl font-bold text-gray-900 mb-2">Review Your Order</h1>
        <p class="text-gray-500 mb-8">Please confirm the items below before placing your order.</p>

        {{-- Order Summary Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h2 class="font-semibold text-gray-900">Order Summary</h2>
            </div>
            <div class="divide-y">
                @foreach($items as $item)
                    <div class="flex items-center justify-between px-6 py-4">
                        <div class="flex items-center gap-4">
                            <div class="bg-indigo-50 rounded-lg w-12 h-12 flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ $item['product']->name }}</p>
                                <p class="text-sm text-gray-500">${{ number_format($item['product']->price, 2) }} &times; {{ $item['quantity'] }}</p>
                            </div>
                        </div>
                        <span class="font-semibold text-gray-900">${{ number_format($item['subtotal'], 2) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Payment Method --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h2 class="font-semibold text-gray-900">Payment Method</h2>
            </div>
            <div class="px-6 py-5">
                <div class="flex items-center gap-3 p-4 border-2 border-indigo-500 rounded-lg bg-indigo-50">
                    <div class="flex items-center justify-center w-10 h-10 bg-indigo-100 rounded-full">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">Cash on Delivery</p>
                        <p class="text-sm text-gray-500">Pay when your order arrives</p>
                    </div>
                    <svg class="w-5 h-5 text-indigo-600 ml-auto" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Total & Actions --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-gray-500">Subtotal</span>
                    <span class="text-gray-900">${{ number_format($total, 2) }}</span>
                </div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-gray-500">Shipping</span>
                    <span class="text-green-600 font-medium">Free</span>
                </div>
                <div class="border-t pt-3 flex items-center justify-between">
                    <span class="text-lg font-bold text-gray-900">Total</span>
                    <span class="text-2xl font-bold text-indigo-600">${{ number_format($total, 2) }}</span>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t flex items-center justify-between">
                <a href="{{ route('cart.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition">
                    &larr; Back to Cart
                </a>
                <form action="{{ route('checkout.process') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-8 py-3 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition shadow-sm flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Place Order
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
