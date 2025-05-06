<?php

namespace App\Http\Requests\AddressBook;

use Illuminate\Foundation\Http\FormRequest;

class ADSAddressBookRequest extends FormRequest
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
            'recipient_ids' => ['required', 'array'],   
            'recipient_ids.*' => ['uuid', 'exists:recipients,id'], // существует ли каждый элемент в базе
        ];
    }
}
