<?php

namespace App\Http\Requests\Message;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MessageSendRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address_book_id' => ['required', 'uuid', 'size:36'],
            'type' => ['required', Rule::in(['message', 'photo', 'document'])],
            'text' => ['required', 'string', 'max:4096'],
            'link' => ['sometimes', 'url'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $type = $this->input('type');
            if (in_array($type, ['photo', 'document']) && ! $this->filled('link')) {
                $validator->errors()->add('link', 'Поле link обязательно для типа '.$type.'.');
            }
        });
    }
}
