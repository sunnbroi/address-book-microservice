<?php

namespace App\Http\Requests\Recipient;

use Illuminate\Foundation\Http\FormRequest;

class DestroyRecipientRequest extends FormRequest
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

            'recipient_ids' => ['required', 'array', 'min:1'],
            'recipient_ids.*' => ['required', 'uuid', 'exists:recipients,id', 'size:36'],
        ];
    }
}
