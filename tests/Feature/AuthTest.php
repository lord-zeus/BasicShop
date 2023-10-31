<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class AuthTest extends TestCase
{

    use DatabaseMigrations;

    /**
     * @return void
     *
     */
    public function test_admin_can_login_and_with_correct_credentials(): void
    {
        User::factory()->create();
        $response = $this->post('/api/v1/login', ['name'=> 'admin', 'password' => 'admin']);
        $response->assertStatus(200);

        //Wrong name
        $response = $this->post('/api/v1/login', ['name'=> 'admins', 'password' => 'admin']);
        $response->assertStatus(401);

        // Wrong Password
        $response = $this->post('/api/v1/login', ['name'=> 'admin', 'password' => 'admins']);
        $response->assertStatus(401);

    }
}
