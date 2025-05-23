<?php

namespace App\Http\Requests\AddressBook;

use Illuminate\Foundation\Http\FormRequest;

class BulkStoreAddressBookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'address_books' => ['required', 'array'],
            'address_books.*.client_key' => ['required', 'exists:clients,client_key', 'size:36'],
            'address_books.*.name' => ['required', 'string', 'max:255'],
        ];
    }
}
