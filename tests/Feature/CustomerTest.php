<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerTest extends TestCase
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

    private function authenticate(string $role = 'cashier'): string
    {
        $this->app['auth']->forgetGuards();

        $user = User::create([
            'name' => ucfirst($role) . ' User',
            'email' => $role . '@icecream.test',
            'password' => Hash::make('password123'),
            'role' => $role,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        return $response->json('token');
    }

    public function test_can_update_customer_with_same_email(): void
    {
        $customer = Customer::create([
            'name' => 'Kasun Wickramasinghe',
            'email' => 'kasun.wickrama@example.com',
            'phone' => '0753456789',
            'address' => '78 Lake Drive, Galle',
        ]);

        $token = $this->authenticate();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson("/api/customers/{$customer->id}", [
                'name' => 'Kasun Wickramasinghe Updated',
                'email' => 'kasun.wickrama@example.com',
                'phone' => '0753456789',
                'address' => '78 Lake Drive, Galle',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Kasun Wickramasinghe Updated',
                'email' => 'kasun.wickrama@example.com',
            ]);
    }

    public function test_cannot_update_customer_with_another_customers_email(): void
    {
        Customer::create([
            'name' => 'Existing Customer',
            'email' => 'existing@example.com',
            'phone' => '0711111111',
            'address' => 'Address 1',
        ]);

        $customer = Customer::create([
            'name' => 'Kasun Wickramasinghe',
            'email' => 'kasun.wickrama@example.com',
            'phone' => '0753456789',
            'address' => '78 Lake Drive, Galle',
        ]);

        $token = $this->authenticate();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson("/api/customers/{$customer->id}", [
                'name' => 'Kasun Wickramasinghe',
                'email' => 'existing@example.com',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_only_owner_can_delete_customer(): void
    {
        $customer = Customer::create([
            'name' => 'To Delete',
            'email' => 'delete@example.com',
            'phone' => '0799999999',
            'address' => 'Address 2',
        ]);

        $cashierToken = $this->authenticate('cashier');
        $response = $this->withHeader('Authorization', 'Bearer ' . $cashierToken)
            ->deleteJson("/api/customers/{$customer->id}");
        $response->assertStatus(403);

        $ownerToken = $this->authenticate('owner');
        $response = $this->withHeader('Authorization', 'Bearer ' . $ownerToken)
            ->deleteJson("/api/customers/{$customer->id}");
        $response->assertStatus(200);
    }
}
