<?php

namespace App\DTOs;

use App\Models\Order;

readonly class CheckoutResult extends ServiceResult
{
    public function __construct(
        bool $success,
        string $message,
        public ?Order $order = null,
    ) {
        parent::__construct($success, $message);
    }

    public static function withOrder(string $message, Order $order): static
    {
        return new static(true, $message, $order);
    }
}
