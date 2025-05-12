<?php

namespace Tests\Feature;

use App\Enums\ChatStatusEnum;
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
            'status' => ChatStatusEnum::OPEN,
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

    public function test_close_chat()
    {
        $site = Site::factory()->create();
        $chat = $site->chats()->create([
            'visitor_name' => 'Teszt Felhasználó',
            'visitor_email' => 'teszt@gmail.com',
            'status' => ChatStatusEnum::OPEN,
        ]);

        $response = $this->withHeaders([
            'Referer' => $site->domain,
        ])->patchJson(route('widget.close', ['site' => $site->uuid, 'chat_id' => $chat->id]));

        $response->assertStatus(200);

        $response->assertJson([
            'message' => __('widget.chat_closed'),
        ]);

        $this->assertDatabaseHas('chats', [
            'id' => $chat->id,
            'status' => ChatStatusEnum::CLOSED,
        ]);
    }
}
