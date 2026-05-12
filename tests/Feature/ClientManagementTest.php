<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_duplicate_client_cannot_be_created(): void
    {
        $receptionist = User::factory()->create(['role' => User::ROLE_RECEPTIONIST]);

        Client::query()->create([
            'first_name' => 'Jamie',
            'last_name' => 'Lopez',
            'email' => 'jamie@example.com',
            'phone' => '09123456789',
            'address' => '123 Main St, Barangay 1, Manila, NCR',
            'notes' => 'Existing client',
        ]);

        $response = $this
            ->actingAs($receptionist)
            ->post(route('clients.store'), [
                'first_name' => 'Jamie',
                'last_name' => 'Lopez',
                'email' => 'another@example.com',
                'phone' => '09123456789',
                'address_house_street' => '456 Side St',
                'address_barangay' => 'Barangay 2',
                'address_city' => 'Quezon City',
                'address_postal_province' => 'NCR',
                'notes' => 'Duplicate attempt',
            ]);

        $response
            ->assertSessionHasErrors('first_name')
            ->assertRedirect();

        $this->assertDatabaseCount('clients', 1);
    }

    public function test_a_client_cannot_be_updated_to_duplicate_another_client(): void
    {
        $receptionist = User::factory()->create(['role' => User::ROLE_RECEPTIONIST]);

        $existingClient = Client::query()->create([
            'first_name' => 'Jamie',
            'last_name' => 'Lopez',
            'email' => 'jamie@example.com',
            'phone' => '09123456789',
            'address' => '123 Main St, Barangay 1, Manila, NCR',
            'notes' => 'Existing client',
        ]);

        $clientToUpdate = Client::query()->create([
            'first_name' => 'Morgan',
            'last_name' => 'Reyes',
            'email' => 'morgan@example.com',
            'phone' => '09987654321',
            'address' => '789 Oak St, Barangay 3, Makati, NCR',
            'notes' => 'Different client',
        ]);

        $response = $this
            ->actingAs($receptionist)
            ->put(route('clients.update', $clientToUpdate), [
                'first_name' => $existingClient->first_name,
                'last_name' => $existingClient->last_name,
                'email' => 'changed@example.com',
                'phone' => $existingClient->phone,
                'address_house_street' => '101 New St',
                'address_barangay' => 'Barangay 4',
                'address_city' => 'Pasig',
                'address_postal_province' => 'NCR',
                'notes' => 'Trying to duplicate',
            ]);

        $response
            ->assertSessionHasErrors('first_name')
            ->assertRedirect();

        $this->assertSame('Morgan', $clientToUpdate->fresh()->first_name);
        $this->assertSame('09987654321', $clientToUpdate->fresh()->phone);
    }
}
