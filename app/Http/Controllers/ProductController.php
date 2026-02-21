<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function index(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $products = Cache::remember('products.all', 60, function () {
            return Product::all();
        });

        return view('products.index', compact('products'));
    }
}
