<?php

namespace App\DTOs;

use App\Models\Product;
use App\ValueObjects\Money;

readonly class CartItem
{
    public Money $subtotal;

    public function __construct(
        public Product $product,
        public int $quantity,
    ) {
        $this->subtotal = Money::fromDecimal($product->price)->multiply($quantity);
    }
}
