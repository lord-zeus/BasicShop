<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
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
}
