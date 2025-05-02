<?php 
namespace App\Services;

use App\DTO\AddressBook\StoreAddressBookDTO;
use App\DTO\AddressBook\UpdateAddressBookDTO;
use App\Models\AddressBook;

class AddressBookService
{
    public function list()
    {
        return AddressBook::where('client_key', request()->get('auth_client')->client_key)->get();
    }

    public function get(string $id)
    {
        return AddressBook::where('id', $id)
            ->where('client_key', request()->get('auth_client')->client_key)
            ->firstOrFail();
    }

    public function create(StoreAddressBookDTO $dto)
    {
        return AddressBook::create([
            'name' => $dto->name,
            'client_key' => $dto->client->client_key,
        ]);
    }

    public function update(string $id, UpdateAddressBookDTO $dto)
    {
        $book = $this->get($id);
        $book->update([
            'name' => $dto->name ?? $book->name,
        ]);
        return $book;
    }

    public function delete(string $id): void
    {
        $this->get($id)->delete();
    }
}
