<?php

namespace App\Http\Controllers;

use App\Services\CheckoutService;

class CheckoutController extends Controller
{
    public function __construct(
        private CheckoutService $checkoutService,
    ) {}

    public function process(): \Illuminate\Http\RedirectResponse
    {
        $result = $this->checkoutService->process(session()->getId());

        return redirect()->route('products.index')->with(
            $result['success'] ? 'success' : 'error',
            $result['message'],
        );
    }
}
