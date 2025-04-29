<?php

namespace App\Http\Requests\Message;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // авторизацию проверяет middleware
    }

    public function rules(): array
    {
        return [
            'address_book_id' => ['required', 'uuid', 'exists:address_books,id'],
            'text' => ['required', 'string', 'max:1000'],
        ];
    }
}
