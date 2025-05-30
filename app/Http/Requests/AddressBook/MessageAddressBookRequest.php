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
        'address_book_id' => ['nullable', 'uuid', 'exists:address_books,id', 'size:36'],
        'recipient_id' => ['nullable', 'uuid', 'exists:recipients,id', 'size:36'],
        'text' => ['nullable', 'string', 'max:4096'],
        'link' => ['string', 'required_if:type,photo,document,video,audio,voice'],
        'type' => ['required', 'string', 'in:message,photo,document,video,audio,voice', 'required_without_all:address_book_id,recipient_id'],
    ];
}


}
