<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * A basic feature test example.
     */
    public function test_admin_can_view_all_products(): void
    {
        User::factory()->create();
        $response = $this->post('/api/v1/login', ['name'=> 'admin', 'password' => 'admin']);
        $data = $response->getOriginalContent();
        $admin_response = $this->withHeaders(['Authorization' => "Bearer $data[data]"])
            ->get('/api/v1/products');
        $admin_response->assertStatus(200);
    }

    public function test_admin_can_create_product_with_valid_fields(): void
    {
        User::factory()->create();

        $response = $this->post('/api/v1/login', ['name'=> 'admin', 'password' => 'admin']);
        $data = $response->getOriginalContent();
        $response = $this->withHeaders(['Authorization' => "Bearer $data[data]"])
            ->post('/api/v1/products', [
                'name' => fake()->text,
                'sku' => Str::slug(fake()->unique()->text),
                'price' => fake()->numberBetween(100, 10000),
                'file' => UploadedFile::fake()->image('test.jpg')

            ]);
        $response->assertStatus(200);
        $response_data = $response->getOriginalContent();
        // to remove the fake created file i delete the product this feature is tested below
        $delete_product = $this->withHeaders(['Authorization' => "Bearer $data[data]"])
            ->delete("/api/v1/products/{$response_data['data']['id']}");
        $delete_product->assertStatus(200);
        $response = $this->withHeaders(['Authorization' => "Bearer $data[data]"])
            ->post('/api/v1/products', [
                'sku' => Str::slug(fake()->unique()->text),
                'price' => fake()->numberBetween(100, 10000),
                'file' => UploadedFile::fake()->image('test.jpg')

            ]);

        $response->assertStatus(422);
        $response->assertSee(['name' => "The name field is required."]);
    }

    public function test_admin_can_view_product():void
    {
        User::factory()->create();
        $product = Product::factory()->create();
        $product_object = json_decode($product);
        $response = $this->post('/api/v1/login', ['name'=> 'admin', 'password' => 'admin']);
        $data = $response->getOriginalContent();

        $response = $this->withHeaders(['Authorization' => "Bearer $data[data]"])
            ->get("/api/v1/products/1");

        $response->assertStatus(200);
        $response->assertSee(['name' => $product_object->name]);
    }

    public function test_admin_can_edit_product():void
    {
        User::factory()->create();
        $product = Product::factory()->create();
        $product_object = json_decode($product);
        $response = $this->post('/api/v1/login', ['name'=> 'admin', 'password' => 'admin']);
        $data = $response->getOriginalContent();

        $response_patch = $this->withHeaders(['Authorization' => "Bearer $data[data]"])
            ->patch("/api/v1/products/$product_object->id", [
                'sku' => Str::slug(fake()->unique()->text),
                'price' => fake()->numberBetween(100, 10000),

            ]);
        $response_put = $this->withHeaders(['Authorization' => "Bearer $data[data]"])
            ->put("/api/v1/products/$product_object->id", [
                'name' => fake()->text,
                'sku' => Str::slug(fake()->unique()->text),
                'price' => fake()->numberBetween(100, 10000),
                'file' => UploadedFile::fake()->image('test.jpg')

            ]);
        //Cleaning left over new image from the PUT reequest
        $delete_product = $this->withHeaders(['Authorization' => "Bearer $data[data]"])
            ->delete("/api/v1/products/$product_object->id");

        $response_patch->assertStatus(200);
        $response_put->assertStatus(200);
        $delete_product->assertStatus(200);


    }

    public function test_admin_can_delete_product_and_file():void
    {
        User::factory()->create();
        $response = $this->post('/api/v1/login', ['name'=> 'admin', 'password' => 'admin']);
        $data = $response->getOriginalContent();
        $response_product = $this->withHeaders(['Authorization' => "Bearer $data[data]"])
            ->post('/api/v1/products', [
                'name' => fake()->text,
                'sku' => Str::slug(fake()->unique()->text),
                'price' => fake()->numberBetween(100, 10000),
                'file' => UploadedFile::fake()->image('test.jpg')

            ]);
        $product_object = $response_product->getOriginalContent();
        $product = $product_object['data'];


        $file_exist_before = Storage::exists(preg_replace("/storage/i", 'public', $product->image));
        $delete_product = $this->withHeaders(['Authorization' => "Bearer $data[data]"])
            ->delete("/api/v1/products/$product->id");
        $file_not_found = Storage::exists(preg_replace("/storage/i", 'public', $product->image));
        $res_product = $this->withHeaders(['Authorization' => "Bearer $data[data]"])
            ->get("/api/v1/products/1");

        $this->assertNotEquals($file_exist_before, $file_not_found);
        $res_product->assertStatus(404);
        $delete_product->assertStatus(200);

    }


    public function test_products_can_be_filtered_by_created_at_and_by_price_then_ordered():void
    {
        User::factory()->create();
        Product::factory()->create();
        sleep(2);
        Product::factory(100)->create();
        sleep(2);
        Product::factory()->create();

        $date_response = $this->get('/api/v1/products/filter/1/20?sort=price');
        $products = $date_response->getContent();
        $first_price = 0;
        $second_price = 0;
        $date_response = $this->get('/api/v1/products/filter/1/20?sort=price&order=desc');
        $products = $date_response->getContent();
        $products_j = json_decode($products, true);
        $third_price = 0;
        $forth_price = 0;
        foreach ($products_j['data']['data'] as $key => $product){
            if($key === 0)
                $third_price = $product['price'];
            else{
                $forth_price = $product['price'];
                break;
            }
        }
        $date_response = $this->get('/api/v1/products/filter/1/20?sort=created_at');
        $products = $date_response->getContent();
        $products_j = json_decode($products, true);
        $first_date = 0;
        $second_date = 0;
        foreach ($products_j['data']['data'] as $key => $product){
            if($key === 0)
                $first_date = Carbon::make($product['created_at']);
            else{
                $second_date = Carbon::make($product['created_at']);
                break;
            }
        }
        $date_response = $this->get('/api/v1/products/filter/1/20?sort=created_at&order=desc');
        $products = $date_response->getContent();
        $products_j = json_decode($products, true);
        $third = 0;
        $forth = 0;
        foreach ($products_j['data']['data'] as $key => $product){
            if($key === 0)
                $third = Carbon::make($product['created_at']);
            else{
                $forth = Carbon::make($product['created_at']);
                break;
            }
        }

        $this->assertGreaterThanOrEqual($first_price, $second_price);
        $this->assertLessThanOrEqual($third_price, $forth_price);
        $this->assertTrue(Carbon::make($first_date)->lt(Carbon::make($second_date)));
        $this->assertTrue(Carbon::make($third)->gt(Carbon::make($forth)));
        $date_response->assertStatus(200);
    }
}
