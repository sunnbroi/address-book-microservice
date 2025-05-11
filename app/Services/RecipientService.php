<?php
namespace App\Services;

use App\Models\AddressBook;
use App\Models\Recipient;
use Illuminate\Support\Str;

class RecipientService
{
    public function createRecipient(array $requstData,  string $addressBookId, string $clientKey):Recipient
    {
        

        return $recipient;
    }

    public function updateRecipient(Recipient $recipient, array $data): Recipient
    {
        $recipient->name = $data['name'];
        $recipient->email = $data['email'];
        $recipient->phone = $data['phone'];
        $recipient->save();

        return $recipient;
    }
}