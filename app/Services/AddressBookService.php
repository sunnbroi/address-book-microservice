<?php

namespace App\Services;

use App\Models\AddressBook;
use App\Models\Recipient;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class AddressBookService
{
    public function findAddressBook(string $id, string $clientKey): ?AddressBook
    {
        return AddressBook::where('id', $id)
            ->where('client_key', $clientKey)
            ->first();
    }

    public function getClientBooks(string $clientKey): Collection
    {
        return AddressBook::where('client_key', $clientKey)->get();
    }

    public function createAddressBook(array $data, string $clientKey): AddressBook
    {
        return AddressBook::create([
            'id' => (string) Str::uuid(),
            'client_key' => $clientKey,
            'name' => $data['name'],
        ]);
    }

    public function updateAddressBook(AddressBook $addressBook, array $data): AddressBook
    {
        if (array_key_exists('invite_key', $data) && $data['invite_key']) {
            $data['invite_key'] = (string) Str::uuid();
        }

        $addressBook->update($data);
        return $addressBook;
    }

    public function deleteAddressBook(AddressBook $addressBook): void
    {
        $addressBook->delete();
    }

    public function bulkStoreAddressBooks(array $addressBooks): void
    {
        AddressBook::insert($addressBooks);
    }

    public function attachRecipients(AddressBook $addressBook, array $recipientIds): void
    {
        $addressBook->recipients()->syncWithoutDetaching($recipientIds);

        // восстановление soft-deleted получателей
        Recipient::onlyTrashed()
            ->whereIn('id', $recipientIds)
            ->restore();
    }

    public function detachRecipients(AddressBook $addressBook, array $recipientIds): void
    {
        $addressBook->recipients()->detach($recipientIds);
    }

    public function syncRecipients(AddressBook $addressBook, array $recipientIds): void
    {
        $addressBook->recipients()->sync($recipientIds);
    }

        public function restoreAddressBookIfExists(string $id, string $clientKey, string $name): ?AddressBook
    {
        $book = AddressBook::onlyTrashed()
            ->where('id', $id)
            ->where('client_key', $clientKey)
            ->first();
    
        if (!$book) {
            return null;
        }
    
        $book->restore();
        $book->update(['name' => $name]);
    
        return $book;
    }

}
