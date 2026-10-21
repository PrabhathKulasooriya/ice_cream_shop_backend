<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('passport:client', [
            '--personal' => true,
            '--name' => 'Testing Personal Access Client',
            '--no-interaction' => true,
        ]);
    }

    public function test_login_requires_email_and_password(): void
    {
        $response = $this->postJson('/auth/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        User::create([
            'name' => 'Test User',
            'email' => 'test@icecream.test',
            'password' => Hash::make('secret123'),
            'role' => 'owner',
        ]);

        $response = $this->postJson('/auth/login', [
            'email' => 'test@icecream.test',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid email or password',
            ]);
    }

    public function test_user_can_login_and_receive_token(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@icecream.test',
            'password' => Hash::make('secret123'),
            'role' => 'owner',
        ]);

        $response = $this->postJson('/auth/login', [
            'email' => 'test@icecream.test',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'token',
                'user' => ['id', 'name', 'email', 'role'],
            ]);

        $this->assertNotEmpty($response->json('token'));
        $this->assertSame($user->email, $response->json('user.email'));
    }

    public function test_unauthenticated_request_to_protected_route_is_rejected(): void
    {
        $response = $this->getJson('/auth/user');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_access_protected_route_with_bearer_token(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@icecream.test',
            'password' => Hash::make('secret123'),
            'role' => 'owner',
        ]);

        $loginResponse = $this->postJson('/auth/login', [
            'email' => 'test@icecream.test',
            'password' => 'secret123',
        ]);

        $token = $loginResponse->json('token');

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/auth/user');

        $response->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'role' => $user->role,
            ]);
    }

    public function test_user_can_logout_and_revoke_token(): void
    {
        User::create([
            'name' => 'Test User',
            'email' => 'test@icecream.test',
            'password' => Hash::make('secret123'),
            'role' => 'owner',
        ]);

        $loginResponse = $this->postJson('/auth/login', [
            'email' => 'test@icecream.test',
            'password' => 'secret123',
        ]);

        $token = $loginResponse->json('token');

        $logoutResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/auth/logout');

        $logoutResponse->assertStatus(200)
            ->assertJson(['message' => 'User logged out']);

        $this->assertEquals(1, DB::table('oauth_access_tokens')->value('revoked'));

        auth()->forgetGuards();

        $subsequentResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/auth/user');

        $subsequentResponse->assertStatus(401);
    }
}
