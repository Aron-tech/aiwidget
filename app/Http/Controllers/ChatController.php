<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChatRequest;
use App\Models\Site;
use App\Enums\ChatStatusEnum;

class ChatController extends Controller
{
    public function show(Site $site, ChatRequest $request) {

        $validated = $request->validated();

        $chat = $site->chats()
            ->where('id', $validated['chat_id'])
            ->whereIn('status', [ChatStatusEnum::OPEN, ChatStatusEnum::WAITING])
            ->first();

        if(empty($chat)) {
            return response()->json([
                'error' => __('widget.missing_chat'),
            ], 404);
        }

        $messages = $chat->messages()->get();

        return response()->json([
            'chat_id' => $chat->id,
            'messages' => $messages,
        ],
            200);

    }

    public function close(Site $site, ChatRequest $request) {
        $validated = $request->validated();

        if(empty($validated['chat_id']))
            return response()->json([
                'error' => __('widget.missing_site'),
            ], 404);

        $chat = $site->chats()->where('id', $validated['chat_id'])->firstOrFail();

        $chat->update([
            'status' => ChatStatusEnum::CLOSED,
        ]);

        return response()->json([
            'message' => __('widget.chat_closed'),
        ],
            200);
    }

    /*public function delete(Site $site, ChatRequest $request) {
        $validated = $request->validated();

        if(empty($site))
            return response()->json([
                'error' => __('widget.missing_site'),
            ], 404);

        $chat = $site->chats()->where('id', $validated['chat_id'])->firstOrFail();

        $chat->delete();

        return response()->json([
            'message' => __('widget.chat_delete'),
        ],
        200);
    }*/
}
