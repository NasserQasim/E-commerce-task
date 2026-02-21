<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartRequest;
use App\Services\CartService;

class CartController extends Controller
{
    public function __construct(
        private CartService $cartService,
    ) {}

    public function index(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $sessionId = session()->getId();
        $items = $this->cartService->getItems($sessionId);
        $total = $this->cartService->getTotal($sessionId);

        return view('cart.index', compact('items', 'total'));
    }

    public function add(AddToCartRequest $request): \Illuminate\Http\RedirectResponse
    {
        $result = $this->cartService->addItem(
            session()->getId(),
            $request->validated('product_id'),
            $request->validated('quantity'),
        );

        return redirect()->back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message'],
        );
    }

    public function update(UpdateCartRequest $request, int $productId): \Illuminate\Http\RedirectResponse
    {
        $result = $this->cartService->updateItem(
            session()->getId(),
            $productId,
            $request->validated('quantity'),
        );

        return redirect()->route('cart.index')->with(
            $result['success'] ? 'success' : 'error',
            $result['message'],
        );
    }

    public function remove(int $productId): \Illuminate\Http\RedirectResponse
    {
        $result = $this->cartService->removeItem(
            session()->getId(),
            $productId,
        );

        return redirect()->route('cart.index')->with(
            $result['success'] ? 'success' : 'error',
            $result['message'],
        );
    }
}
