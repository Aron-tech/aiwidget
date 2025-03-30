<?php

namespace Database\Seeders;

use App\Enums\KeyTypesEnum;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Key;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Papp Áron',
            'email' => 'admin@paron.hu',
            'password' => bcrypt('password'),
        ]);

        Key::factory()->create([
            'user_id' => 1,
            'site_id' => null,
            'token' => hash('sha256', 'key'),
            'type' => KeyTypesEnum::DEVELOPER->value,
            'expiration_time' => now()->addYear(),
        ]);
    }
}
