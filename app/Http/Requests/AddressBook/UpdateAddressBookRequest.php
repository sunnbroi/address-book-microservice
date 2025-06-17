<?php

namespace App\Http\Requests\AddressBook;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAddressBookRequest extends FormRequest
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

    // этот request используется в методах update и store контроллера AddressBookController, при у
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'invite_key' => ['nullable', 'string'],
        ];
    }
}
