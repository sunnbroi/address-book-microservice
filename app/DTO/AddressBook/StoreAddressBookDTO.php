<?php
namespace App\DTO\AddressBook;

use App\Models\Client;
use Illuminate\Http\Request;

class StoreAddressBookDTO
{
    public function __construct(public string $name, public Client $client) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->input('name'),
            client: $request->get('auth_client')
        );
    }
}
