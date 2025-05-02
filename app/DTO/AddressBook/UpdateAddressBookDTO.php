<?php
namespace App\DTO\AddressBook;

use Illuminate\Http\Request;

class UpdateAddressBookDTO
{
    public function __construct(public ?string $name = null) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->input('name')
        );
    }
}
