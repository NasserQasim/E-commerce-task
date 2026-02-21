<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
    ) {}

    public function index(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $products = Cache::remember('products.all', 60, function () {
            return $this->productRepository->all();
        });

        return view('products.index', compact('products'));
    }
}
