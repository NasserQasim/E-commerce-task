<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Wireless Bluetooth Headphones',
                'price' => 79.99,
                'stock_quantity' => 50,
            ],
            [
                'name' => 'USB-C Hub Adapter',
                'price' => 34.99,
                'stock_quantity' => 100,
            ],
            [
                'name' => 'Mechanical Keyboard',
                'price' => 129.99,
                'stock_quantity' => 30,
            ],
            [
                'name' => 'Ergonomic Mouse',
                'price' => 49.99,
                'stock_quantity' => 75,
            ],
            [
                'name' => '27" 4K Monitor',
                'price' => 399.99,
                'stock_quantity' => 15,
            ],
            [
                'name' => 'Laptop Stand',
                'price' => 29.99,
                'stock_quantity' => 120,
            ],
            [
                'name' => 'Webcam HD 1080p',
                'price' => 59.99,
                'stock_quantity' => 60,
            ],
            [
                'name' => 'Portable SSD 1TB',
                'price' => 89.99,
                'stock_quantity' => 40,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
