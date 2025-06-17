<?php

namespace App\Http\Requests\Recipient;

use Illuminate\Foundation\Http\FormRequest;

class BulkStoreRecipientRequest extends FormRequest
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
            'recipients' => ['required', 'array', 'min:1'],
            'recipients.*.telegram_user_id' => ['required', 'string', 'max:255', 'unique:recipients,telegram_user_id'],
        ];
    }
}
