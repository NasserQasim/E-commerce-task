<?php

namespace App\Http\Controllers;

use App\Actions\ProcessCheckoutAction;
use App\Services\CartService;

class CheckoutController extends Controller
{
    public function __construct(
        private ProcessCheckoutAction $processCheckoutAction,
        private CartService $cartService,
    ) {}

    public function show()
    {
        $sessionId = session()->getId();
        $items = $this->cartService->getItems($sessionId);

        if (empty($items)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $total = $this->cartService->getTotal($sessionId, $items);

        return view('checkout.confirm', compact('items', 'total'));
    }

    public function process()
    {
        $result = $this->processCheckoutAction->execute(session()->getId());

        return redirect()->route('products.index')->with(
            $result->success ? 'success' : 'error',
            $result->message,
        );
    }
}
