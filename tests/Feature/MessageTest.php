<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Site;
use App\Models\Chat;
use App\Models\Message;

class MessageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_submit_message_to_widget()
    {
        $site = Site::factory()->create();

        $chat = Chat::create([
            'site_id' => $site->id,
            'visitor_name' => 'Teszt Felhasználó',
            'visitor_email' => 'teszt@example.com',
        ]);

        $response = $this->postJson(route('widget.store', ['site' => $site->uuid]), [
            'nickname' => 'Teszt Felhasználó',
            'email' => 'teszt@example.com',
            'message' => 'Hello, this is a test message!',
            'chat_id' => $chat->id,
        ], [
            'Referer' => $site->domain,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('messages', [
            'chat_id' => $chat->id,
            'message' => 'Hello, this is a test message!',
        ]);

        $response->assertJsonFragment([
            'data' => [
                'chat_id' => $chat->id,
            ]
        ]);
    }
}
