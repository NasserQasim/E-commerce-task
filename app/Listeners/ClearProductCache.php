<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Events\OrderRefunded;
use Illuminate\Events\Attributes\AsEventListener;
use Illuminate\Support\Facades\Cache;

#[AsEventListener(event: OrderPlaced::class)]
#[AsEventListener(event: OrderRefunded::class)]
class ClearProductCache
{
    public function handle(OrderPlaced|OrderRefunded $event): void
    {
        Cache::forget('products.all');
    }
}
