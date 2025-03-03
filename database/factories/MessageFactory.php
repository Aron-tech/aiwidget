<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'chat_id' => \App\Models\Chat::factory(),
            'message' => $this->faker->sentence(),
            'sender_role' => $this->faker->randomElement(['user', 'bot', 'admin']),
        ];
    }
}
