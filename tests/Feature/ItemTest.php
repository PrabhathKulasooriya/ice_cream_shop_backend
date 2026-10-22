<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ItemTest extends TestCase
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

    private function authenticateAs(string $role): string
    {
        $user = User::create([
            'name' => ucfirst($role).' User',
            'email' => $role.'@icecream.test',
            'password' => Hash::make('password123'),
            'role' => $role,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        return $response->json('token');
    }

    public function test_unauthenticated_user_cannot_access_items(): void
    {
        $response = $this->getJson('/api/items');
        $response->assertStatus(401);
    }

    public function test_every_user_role_can_retrieve_items(): void
    {
        $category = Category::create(['name' => 'Ice Creams']);
        Item::create([
            'category_id' => $category->id,
            'name' => 'Vanilla Bean',
            'description' => 'Classic rich vanilla',
            'price' => 3.50,
            'quantity' => 20,
        ]);

        foreach (['owner', 'manager', 'cashier'] as $role) {
            $token = $this->authenticateAs($role);

            $response = $this->withHeader('Authorization', 'Bearer '.$token)
                ->getJson('/api/items');

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'items' => [
                        '*' => ['id', 'category_id', 'name', 'description', 'price', 'quantity', 'category'],
                    ],
                ])
                ->assertJsonFragment(['name' => 'Vanilla Bean']);
        }
    }

    public function test_cashier_cannot_create_item(): void
    {
        $category = Category::create(['name' => 'Waffles']);
        $cashierToken = $this->authenticateAs('cashier');

        $response = $this->withHeader('Authorization', 'Bearer '.$cashierToken)
            ->postJson('/api/items', [
                'category_id' => $category->id,
                'name' => 'Belgian Waffle Cone',
                'description' => 'Freshly baked waffle cone',
                'price' => 1.75,
                'quantity' => 50,
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Forbidden. You do not have permission to access this resource.',
            ]);
    }

    public function test_owner_can_create_item(): void
    {
        $category = Category::create(['name' => 'Sundaes']);
        $ownerToken = $this->authenticateAs('owner');

        $response = $this->withHeader('Authorization', 'Bearer '.$ownerToken)
            ->postJson('/api/items', [
                'category_id' => $category->id,
                'name' => 'Hot Fudge Sundae',
                'description' => 'Sundae with hot fudge',
                'price' => 5.25,
                'quantity' => 15,
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'message' => 'Item created successfully',
                'name' => 'Hot Fudge Sundae',
            ]);

        $this->assertDatabaseHas('items', [
            'name' => 'Hot Fudge Sundae',
            'category_id' => $category->id,
        ]);
    }

    public function test_manager_can_create_item(): void
    {
        $category = Category::create(['name' => 'Gelato']);
        $managerToken = $this->authenticateAs('manager');

        $response = $this->withHeader('Authorization', 'Bearer '.$managerToken)
            ->postJson('/api/items', [
                'category_id' => $category->id,
                'name' => 'Pistachio Gelato',
                'description' => 'Creamy pistachio',
                'price' => 4.50,
                'quantity' => 10,
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'message' => 'Item created successfully',
                'name' => 'Pistachio Gelato',
            ]);

        $this->assertDatabaseHas('items', [
            'name' => 'Pistachio Gelato',
            'category_id' => $category->id,
        ]);
    }

    public function test_create_item_validates_required_fields(): void
    {
        $ownerToken = $this->authenticateAs('owner');

        $response = $this->withHeader('Authorization', 'Bearer '.$ownerToken)
            ->postJson('/api/items', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category_id', 'name', 'price']);
    }

    public function test_unauthenticated_user_cannot_update_item(): void
    {
        $category = Category::create(['name' => 'Ice Creams']);
        $item = Item::create([
            'category_id' => $category->id,
            'name' => 'Chocolate Fudge',
            'price' => 4.00,
            'quantity' => 10,
        ]);

        $response = $this->putJson("/api/items/{$item->id}", [
            'name' => 'Updated Chocolate Fudge',
        ]);

        $response->assertStatus(401);
    }

    public function test_cashier_cannot_update_item(): void
    {
        $category = Category::create(['name' => 'Ice Creams']);
        $item = Item::create([
            'category_id' => $category->id,
            'name' => 'Chocolate Fudge',
            'price' => 4.00,
            'quantity' => 10,
        ]);

        $cashierToken = $this->authenticateAs('cashier');

        $response = $this->withHeader('Authorization', 'Bearer '.$cashierToken)
            ->putJson("/api/items/{$item->id}", [
                'name' => 'Updated Chocolate Fudge',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Forbidden. You do not have permission to access this resource.',
            ]);
    }

    public function test_owner_and_manager_can_update_item(): void
    {
        $category = Category::create(['name' => 'Ice Creams']);
        $item = Item::create([
            'category_id' => $category->id,
            'name' => 'Original Name',
            'price' => 2.50,
            'quantity' => 5,
        ]);

        foreach (['owner', 'manager'] as $role) {
            $token = $this->authenticateAs($role);

            $response = $this->withHeader('Authorization', 'Bearer '.$token)
                ->putJson("/api/items/{$item->id}", [
                    'name' => "Updated by {$role}",
                    'price' => 3.75,
                ]);

            $response->assertStatus(200)
                ->assertJsonFragment([
                    'message' => 'Item updated successfully',
                    'name' => "Updated by {$role}",
                ]);

            $this->assertDatabaseHas('items', [
                'id' => $item->id,
                'name' => "Updated by {$role}",
            ]);
        }
    }

    public function test_update_item_validates_input(): void
    {
        $category = Category::create(['name' => 'Ice Creams']);
        $item = Item::create([
            'category_id' => $category->id,
            'name' => 'Valid Item',
            'price' => 3.00,
            'quantity' => 5,
        ]);

        $ownerToken = $this->authenticateAs('owner');

        $response = $this->withHeader('Authorization', 'Bearer '.$ownerToken)
            ->putJson("/api/items/{$item->id}", [
                'category_id' => 9999, // Non-existent category
                'price' => -5,        // Invalid negative price
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category_id', 'price']);
    }

    public function test_update_non_existent_item_returns_404(): void
    {
        $ownerToken = $this->authenticateAs('owner');

        $response = $this->withHeader('Authorization', 'Bearer '.$ownerToken)
            ->putJson('/api/items/99999', [
                'name' => 'Non existent',
            ]);

        $response->assertStatus(404);
    }
}
