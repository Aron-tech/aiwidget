<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\KeyTypesEnum;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Key>
 */
class KeyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'token' => $this->faker->uuid(),
            'type' => $this->faker->randomElement([KeyTypesEnum::MODERATOR->value, KeyTypesEnum::OWNER->value]),
            'expiration_time' => now()->addYear(),
        ];
    }
}
