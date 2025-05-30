<?php

namespace App\Services;

use App\Models\Recipient;
use App\Models\AddressBook;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class RecipientService
{
    public function getRecipientsByAddressBook(string $clientKey, string $addressBookId): ?Collection
    {
        $addressBook = AddressBook::where('client_key', $clientKey)
            ->where('id', $addressBookId)
            ->first();

        if (!$addressBook) {
            return null;
        }

        return $addressBook->recipients()->get();
    }

    public function createRecipient(array $data, string $clientKey, string $addressBookId): ?Recipient
    {
        $addressBook = AddressBook::where('client_key', $clientKey)
            ->where('id', $addressBookId)
            ->first();

        if (!$addressBook) {
            return null;
        }

        $recipient = Recipient::create([
            'id'         => (string) Str::uuid(),
            'chat_id'    => $data['chat_id'],
            'username'   => $data['username'] ?? null,
            'first_name' => $data['first_name'] ?? null,
            'last_name'  => $data['last_name'] ?? null,
            'type'       => $data['type'] ?? null,
        ]);

        $addressBook->recipients()->attach($recipient->id);

        return $recipient;
    }

    public function deleteRecipient(string $clientKey, string $addressBookId, string $recipientId): bool
    {
        $addressBook = AddressBook::where('client_key', $clientKey)
            ->where('id', $addressBookId)
            ->first();

        if (!$addressBook) {
            return false;
        }

        $recipient = Recipient::where('id', $recipientId)
            ->whereHas('addressBooks', function ($q) use ($addressBookId) {
                $q->where('address_books.id', $addressBookId);
            })
            ->first();

        if (!$recipient) {
            return false;
        }

        $recipient->addressBooks()->detach($addressBookId);
        $recipient->delete();

        return true;
    }

    public function bulkStoreRecipients(array $recipientData): void
    {
        $prepared = collect($recipientData)->map(function ($item) {
            return array_merge($item, [
                'id' => (string) Str::uuid()
            ]);
        });

        Recipient::insert($prepared->toArray());
    }

    public function updateRecipient(Recipient $recipient, array $data): Recipient
    {
        $recipient->update($data);
        return $recipient;
    }

    public function attachAddressBooks(Recipient $recipient, array $addressBookIds): void
    {
        $recipient->addressBooks()->syncWithoutDetaching($addressBookIds);

        if ($recipient->trashed()) {
            $recipient->restore();
        }
    }

    public function detachAddressBooks(Recipient $recipient, array $addressBookIds): void
    {
        $recipient->addressBooks()->detach($addressBookIds);
    }

    public function syncAddressBooks(Recipient $recipient, array $addressBookIds): void
    {
        $recipient->addressBooks()->sync($addressBookIds);
    }
}
