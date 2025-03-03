<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Site;

class ChatController extends Controller
{
    public function show(Request $request, Site $site) {

        $validated = $request->validate([
            'chat_id' => 'required|exists:chats,id',
        ]);

        $chat = $site->chats()
            ->where('id', $validated['chat_id'])
            ->whereIn('status', [1, 2])
            ->first();

        if(empty($chat)) {
            return response()->json([
                'error' => 'A beszélgetés betöltése sikertelen volt. A beszélgetés nem létezik!',
            ], 404);
        }

        $messages = $chat->messages()->get();

        return response()->json([
            'chat_id' => $chat->id,
            'messages' => $messages,
        ],
        200);

    }

    public function delete(Request $request, Site $site) {
        $validated = $request->validate([
            'chat_id' => 'required|exists:chats,id',
        ]);

        if(empty($site))
            return response()->json([
                'error' => 'A webhely nem található!',
            ], 404);

        $chat = $site->chats()->where('id', $validated['chat_id'])->firstOrFail();

        $chat->delete();

        return response()->json([
            'message' => 'Chat sikeresen törölve.',
        ],
        200);
    }
}
