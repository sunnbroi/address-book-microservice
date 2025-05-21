<?php
namespace App\Services;

use App\Models\AddressBook;
use App\Models\Recipient;
use Illuminate\Support\Str;
use App\HTTP\Requests\Recipient\StoreRecipientRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecipientService
{
    public function createRecipient(StoreRecipientRequest $request,  string $addressBookId): ?Recipient
    {
        $clientKey = $request->header('X-Client-Key');
        $validated = $request->validated();
        $addressBook = AddressBook::where('id', $addressBookId)
            ->where('client_key', $clientKey)->first();
        if (!$addressBook) {return response()->json(['message' => 'Address book not found'], 404);}

        $data = [
            'id' => (string) Str::uuid(),
            'chat_id' => $validated['chat_id'],
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
    public function detachRecipient(Request $request, string $idAddressBook, string $idRecipient): JsonResponse
    {
        $clientKey = $request->header('X-Client-Key');
        $request = $request->input();
        if(!$idAddressBook || !$idRecipient){
            return response()->json(['message' => 'No IDs provided for deletion'], 400);        
        }else{
        $addressBook = AddressBook::where('client_key', $clientKey)
        ->where('id', $idAddressBook)->first();
        }
        if (!$addressBook) {
            return response()->json(['message' => 'Address book not found'], 404);
        }
        $recipient = Recipient::where('id', $idRecipient)
        ->whereHas('addressBooks', function ($query) use ($idAddressBook) {
            $query->where('address_books.id', $idAddressBook);
        })
        ->first();
        if (!$recipient) {
            return response()->json(['message' => 'Recipient not found'], 404);
        }
        $recipient->addressBooks()->detach($idAddressBook);
        return response()->json(['message' => 'Recipient detached from address book'], 200);
    }
    
}