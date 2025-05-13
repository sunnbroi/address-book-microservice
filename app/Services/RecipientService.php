<?php
namespace App\Services;

use App\Models\AddressBook;
use App\Models\Recipient;
use Illuminate\Support\Str;

class RecipientService
{
    public function createRecipient(array $validated,  string $addressBookId, string $clientKey): ?Recipient
    {
        $addressBook = AddressBook::where('id', $addressBookId)
            ->where('client_key', $clientKey)->first();
        if (!$addressBook) {return response()->json(['message' => 'Address book not found'], 404);}

        $data = [
            'id' => (string) Str::uuid(),
            'telegram_user_id' => $validated['telegram_user_id'],
        ];
        foreach (['username', 'first_name', 'last_name', 'type'] as $field) {
            if (isset($validated[$field])) {
                $data[$field] = $validated[$field];
            }
            $recipient = Recipient::create($data);
            $addressBook->recipients()->attach($recipient->id);
            return $recipient;
        }
    }
}