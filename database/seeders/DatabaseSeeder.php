<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::where('name', 'admin')->first();
        if(empty($admin)){
            User::factory()->create();
        }
        Product::factory(100)->create();
        OrderProduct::factory(100)->create();
        Order::factory(30)->create();

    }
}
