<?php

namespace Database\Factories;

use App\Enums\BalanceTransactionTypeEnum;
use App\Models\Balance;
use App\Models\Key;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BalanceFactory extends Factory
{
    protected $model = Balance::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(), // kapcsolódó user
            'key_id' => Key::factory(),   // kapcsolódó key, ha van
            'amount' => $this->faker->randomFloat(2, 0, 1000),
            'type' => $this->faker->randomElement([BalanceTransactionTypeEnum::cases()]),
            'description' => $this->faker->sentence(),
        ];
    }
}
