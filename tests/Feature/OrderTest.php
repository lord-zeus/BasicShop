<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
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

    public function test_admin_can_view_order_and_order_products():void
    {
        Product::factory(20)->create();
        Order::factory(20)->create();
        OrderProduct::factory(100)->create();
        User::factory()->create();
        $response = $this->post('/api/v1/login', ['name'=> 'admin', 'password' => 'admin']);
        $data = $response->getOriginalContent();

        $response = $this->withHeaders(['Authorization' => "Bearer $data[data]"])
            ->getJson('/api/v1/orders/1');
        $values = $response->getContent();
        $values_j = json_decode($values, true);
        $order_products = $values_j['data']['order_products'];

        $response->assertStatus(200)
            ->assertJsonIsArray("data.order_products");
        $this->assertEquals($order_products[0]['order_id'], 1);

    }

    public function test_admin_can_filter_orders_by_create_at_and_amount(){
        User::factory()->create();
        Product::factory(20)->create();
        Order::factory()->create();
        sleep(2);
        Order::factory(60)->create();
        sleep(2);
        Order::factory()->create();
        OrderProduct::factory(100)->create();
        $response = $this->post('/api/v1/login', ['name'=> 'admin', 'password' => 'admin']);
        $data = $response->getOriginalContent();



        $date_response = $this->withHeaders(['Authorization' => "Bearer $data[data]"])
            ->get('/api/v1/orders/filter/1/20?sort=amount');
        $orders = $date_response->getContent();
        $first_amount = 0;
        $second_amount = 0;
        $date_response = $this->withHeaders(['Authorization' => "Bearer $data[data]"])
            ->get('/api/v1/orders/filter/1/20?sort=amount&order=desc');
        $orders = $date_response->getContent();
        $orders_j = json_decode($orders, true);
        $third_amount = 0;
        $forth_amount = 0;
        foreach ($orders_j['data']['data'] as $key => $order){
            if($key === 0)
                $third_amount = $order['amount'];
            else{
                $forth_amount = $order['amount'];
                break;
            }
        }
        $date_response = $this->withHeaders(['Authorization' => "Bearer $data[data]"])
            ->get('/api/v1/orders/filter/1/20?sort=created_at');
        $orders = $date_response->getContent();
        $orders_j = json_decode($orders, true);
        $first_date = 0;
        $second_date = 0;
        foreach ($orders_j['data']['data'] as $key => $order){
            if($key === 0)
                $first_date = Carbon::make($order['created_at']);
            else{
                $second_date = Carbon::make($order['created_at']);
                break;
            }
        }
        $date_response = $this->withHeaders(['Authorization' => "Bearer $data[data]"])
            ->get('/api/v1/orders/filter/1/20?sort=created_at&order=desc');
        $orders = $date_response->getContent();
        $orders_j = json_decode($orders, true);
        $third = 0;
        $forth = 0;
        foreach ($orders_j['data']['data'] as $key => $order){
            if($key === 0)
                $third = Carbon::make($order['created_at']);
            else{
                $forth = Carbon::make($order['created_at']);
                break;
            }
        }

        $this->assertGreaterThanOrEqual($first_amount, $second_amount);
        $this->assertLessThanOrEqual($third_amount, $forth_amount);
        $this->assertTrue(Carbon::make($first_date)->lt(Carbon::make($second_date)));
        $this->assertTrue(Carbon::make($third)->gt(Carbon::make($forth)));
        $date_response->assertStatus(200);
    }
}
