<?php

namespace App\Http\Requests\Recipient;

use Illuminate\Foundation\Http\FormRequest;

class ADSRecipientRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'address_book_ids' => ['required', 'array'],
            'address_book_ids.*' => ['uuid', 'exists:address_books,id', 'size:36'], // существует ли каждый элемент в базе
        ];
    }
}
