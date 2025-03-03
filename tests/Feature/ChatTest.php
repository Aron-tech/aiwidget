<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Site;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_valid_chat()
    {
        $site = Site::factory()->create();
        $chat = $site->chats()->create([
            'visitor_name' => 'Teszt Felhasználó',
            'visitor_email' => 'teszt@gmail.com',
            'status' => 1,
        ]);
        $message = $chat->messages()->create([
            'message' => 'Test message',
        ]);

        $response = $this->get(route('widget.show', ['site' => $site->uuid, 'chat_id' => $chat->id]), [
            'Referer' => $site->domain,
        ]);

        $response->assertStatus(200);

        $response->assertJson([
            'chat_id' => $chat->id,
            'messages' => [
                [
                    'message' => $message->message,
                ]
            ]
        ]);
    }

    public function test_delete_chat()
    {
        $site = Site::factory()->create();
        $chat = $site->chats()->create([
            'visitor_name' => 'Teszt Felhasználó',
            'visitor_email' => 'teszt@gmail.com',
            'status' => 1,
        ]);

        $response = $this->withHeaders([
            'Referer' => $site->domain,
        ])->deleteJson(route('widget.delete', ['site' => $site->uuid, 'chat_id' => $chat->id]));

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 'Chat sikeresen törölve.',
        ]);

        $this->assertDatabaseMissing('chats', ['id' => $chat->id]);
    }
}
