<?php

namespace App\Http\Requests\AddressBook;

use Illuminate\Foundation\Http\FormRequest;

class MessageAddressBookRequest extends FormRequest
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
            'address_book_id' => 'nullable|uuid|exists:address_books,id',
            'chat_id' => 'nullable|uuid|exists:recipients,id',
            'type' => 'required|string|in:message,photo,document',
            'message' => 'nullable|string', // больше не required_if
            'file' => 'required_if:type,photo,document', // обязательно только для фото/документов
        ];
    }
}
