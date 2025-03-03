<?php

namespace Tests\Feature\Auth;

use App\Models\Key;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = Volt::test('auth.register')
            ->set('first_name', 'John')
            ->set('last_name', 'Doe')
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');

        // Ellenőrizzük, hogy nincsenek hibák, és átirányít a dashboardra
        $response
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        // Ellenőrizzük, hogy a felhasználó be van-e jelentkezve
        $this->assertAuthenticated();
    }
}
