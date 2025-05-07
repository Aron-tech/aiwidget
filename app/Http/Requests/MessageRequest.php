<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MessageRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'nickname' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'message' => 'required|min:6|string',
            'chat_id' => 'nullable|max:255',
        ];
    }
}
