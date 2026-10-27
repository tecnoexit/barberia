<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Barber;
use App\Models\Service;
use App\Models\User;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $this->seed();
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_only_admin_can_open_administration(): void
    {
        $this->seed();
        $barber = User::where('role', 'barber')->first();
        $this->actingAs($barber)->get('/panel/admin')->assertForbidden();
        $this->actingAs(User::where('role', 'admin')->first())->get('/panel/admin')->assertOk();
    }

    public function test_admin_can_create_a_manual_appointment(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->first();
        $this->actingAs($admin)->post('/panel/admin/turnos', [
            'barber_id' => Barber::first()->id,
            'service_id' => Service::first()->id,
            'client_name' => 'Cliente de prueba',
            'client_phone' => '5491100000000',
            'date' => now()->addDay()->toDateString(),
            'start_time' => '10:00',
        ])->assertRedirect();
        $this->assertDatabaseHas('appointments', ['client_name' => 'Cliente de prueba', 'start_time' => '10:00']);
    }
}
