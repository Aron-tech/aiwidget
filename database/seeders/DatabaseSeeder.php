<?php

namespace Database\Seeders;

use App\Enums\BalanceTransactionTypeEnum;
use App\Enums\KeyTypesEnum;
use App\Models\Balance;
use App\Models\Site;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Key;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ConversiveAI weboldal tulajdonosa (ügyfél kulccsal rendelkező felhasználó)
        $user = User::factory()->create([
            'name' => 'Ügyfél felhasználó',
            'email' => 'admin@paron.hu',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);

        // ConversiveAI weboldal
        $site = Site::factory()->create([
            'name' => 'ConversiveAI weboldal',
            'uuid' => 'u62OAeWsk3UBBy5fAzY9aAMh',
            'domain' => 'http://127.0.0.1:8000/',
        ]);

        // Aktivált ügyfél kulcs
        $key = Key::factory()->create([
            'user_id' => $user->id,
            'site_id' => $site->id,
            'token' => hash('sha256', 'customerkey2025'),
            'type' => KeyTypesEnum::CUSTOMER->value,
            'expiration_time' => now()->addYear(3),
        ]);

        Balance::factory()->create([
            'user_id' => $user->id,
            'key_id'  => $key->id,
            'amount'  => 150,
            'type'    => BalanceTransactionTypeEnum::DEPOSIT,
            'description' => __('interface.deposit'),
        ]);

        // ConversiveAI weboldalhoz moderátor kulccsal rendelkező felhasználó
        $user2 = User::factory()->create([
            'name' => 'Moderátor felhasználó',
            'email' => 'test@paron.hu',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);

        // Aktivált moderátor kulcs
        Key::factory()->create([
            'user_id' => $user2->id,
            'site_id' => $site->id,
            'token' => hash('sha256', 'moderatorkey2025'),
            'type' => KeyTypesEnum::MODERATOR->value,
            'expiration_time' => now()->addYear(),
        ]);

        // Teljesen üres felhasználó
        $user3 = User::factory()->create([
            'name' => 'Üres Felhasználó',
            'email' => 'ures@paron.hu',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);

        // Még nem használt ügyfél kulcs
        Key::factory()->create([
            'user_id' => null,
            'site_id' => null,
            'token'   => hash('sha256', 'customerkey'),
            'type' => KeyTypesEnum::CUSTOMER->value,
            'expiration_time' => now()->addYear(),
        ]);

        Key::factory()->create([
            'user_id' => null,
            'site_id' => $site->id,
            'token'   => hash('sha256', 'moderatorkey'),
            'type' => KeyTypesEnum::MODERATOR->value,
            'expiration_time' => now()->addYear(),
        ]);

    }
}
