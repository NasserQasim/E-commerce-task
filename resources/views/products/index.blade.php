@extends('layouts.app')

@section('title', 'Products - ShopFlow')

@section('content')
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Our Products</h1>
        <p class="mt-2 text-gray-500">Browse our curated selection of tech essentials.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @forelse($products as $product)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition">
                <div class="bg-gradient-to-br from-indigo-50 to-purple-50 h-40 flex items-center justify-center">
                    <svg class="w-16 h-16 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div class="p-5">
                    <h3 class="font-semibold text-gray-900 text-lg">{{ $product->name }}</h3>
                    <div class="flex items-center justify-between mt-3">
                        <span class="text-2xl font-bold text-indigo-600">${{ number_format($product->price, 2) }}</span>
                        <span class="text-sm {{ $product->stock_quantity > 0 ? 'text-green-600' : 'text-red-500' }}">
                            {{ $product->stock_quantity > 0 ? $product->stock_quantity . ' in stock' : 'Out of stock' }}
                        </span>
                    </div>

                    @if($product->stock_quantity > 0)
                        <form action="{{ route('cart.add') }}" method="POST" class="mt-4" id="add-form-{{ $product->id }}">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="quantity" id="qty-input-{{ $product->id }}" value="1">
                            <div class="flex items-center gap-2">
                                <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden">
                                    <button type="button"
                                            onclick="changeQty({{ $product->id }}, -1, {{ min($product->stock_quantity, 99) }})"
                                            class="px-3 py-2 text-gray-600 hover:bg-gray-100 transition text-sm font-bold">
                                        &minus;
                                    </button>
                                    <span id="qty-display-{{ $product->id }}" class="px-3 py-2 text-sm font-medium text-gray-900 min-w-[2rem] text-center">1</span>
                                    <button type="button"
                                            onclick="changeQty({{ $product->id }}, 1, {{ min($product->stock_quantity, 99) }})"
                                            class="px-3 py-2 text-gray-600 hover:bg-gray-100 transition text-sm font-bold">
                                        +
                                    </button>
                                </div>
                                <button type="submit" class="flex-1 bg-indigo-600 text-white py-2 px-4 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                                    Add to Cart
                                </button>
                            </div>
                        </form>
                    @else
                        <button disabled class="mt-4 w-full bg-gray-100 text-gray-400 py-2 px-4 rounded-lg text-sm cursor-not-allowed">
                            Out of Stock
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-16 text-gray-400">
                <p class="text-lg">No products available.</p>
            </div>
        @endforelse
    </div>

    <script>
        const debounceTimers = {};

        function changeQty(productId, delta, max) {
            const input = document.getElementById('qty-input-' + productId);
            const display = document.getElementById('qty-display-' + productId);
            let current = parseInt(input.value) || 1;
            let newVal = Math.max(1, Math.min(current + delta, max));

            input.value = newVal;
            display.textContent = newVal;

            // Debounce: reset the visual feedback after rapid clicks
            clearTimeout(debounceTimers[productId]);
            display.classList.add('text-indigo-600');
            debounceTimers[productId] = setTimeout(function () {
                display.classList.remove('text-indigo-600');
            }, 300);
        }
    </script>
@endsection
