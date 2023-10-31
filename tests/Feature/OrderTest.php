<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * test
     */

    public function test_admin_can_view_all_orders():void
    {
        User::factory()->create();
        Order::factory(2)->create();
        $response = $this->post('/api/v1/login', ['name'=> 'admin', 'password' => 'admin']);
        $data = $response->getOriginalContent();
        $response_order = $this->withHeaders(['Authorization' => "Bearer $data[data]"])
            ->get('/api/v1/orders');
        $response_order->assertStatus(200);
    }

    public function test_order_can_be_placed_with_the_right_information():void
    {
        Product::factory(5)->create();
        $response_order = $this->post('/api/v1/orders', [
            'phone' => fake()->phoneNumber,
            'comment' => fake()->text,
            'products' => [
                ['id' => 1, 'quantity' => 2],
                ['id' => 2, 'quantity' => 3]
            ]
        ]);

        $response_order_f = $this->post('/api/v1/orders', [
            'comment' => fake()->text,
            'products' => [
                ['id' => 1, 'quantity' => 2],
                ['id' => 2, 'quantity' => 3]
            ]
        ]);

        $response_order->assertStatus(200);
        $response_order_f->assertStatus(422);

    }
}
