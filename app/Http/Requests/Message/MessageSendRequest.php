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
            'address_book_id' => ['required', 'uuid', 'size:36',],
            'type' => ['required', Rule::in(['message', 'photo', 'document'])],
            'text' => ['required', 'string', 'max:4096'],
            'link' => [
                'sometimes',
                'url',
                Rule::requiredIf(function () {
                    $type = $this->input('type', 'message');
                    return in_array($type, ['photo', 'document']);
                }),
            ],
        ];
    }
}
