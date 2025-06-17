<?php

namespace App\Http\Requests\Message;

use Illuminate\Foundation\Http\FormRequest;

class MessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->filled('address_book_id') && ! $this->filled('recipient_id')) {
                $validator->errors()->add('address_book_id', 'Укажите хотя бы адресную книгу или получателя.');
            }
        });
    }

    public function rules(): array
    {
        return [
            'address_book_id' => ['nullable', 'uuid', 'exists:address_books,id', 'max:36'],
            'recipient_id' => ['nullable', 'uuid', 'exists:recipients,id', 'max:36'],
            'text' => ['nullable', 'string', 'max:4096'],
            'link' => ['required_if:type,photo,document,video,audio,voice', 'string'],
            'type' => ['required', 'string', 'in:message,photo,document,video,audio,voice'],
        ];
    }
}
