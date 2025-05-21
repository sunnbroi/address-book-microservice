<?php

namespace App\Http\Controllers;

use App\Http\Requests\Recipient\DestroyRecipientRequest;
use App\HTTP\Requests\Recipient\StoreRecipientRequest;
use App\HTTP\Requests\Recipient\UpdateRecipientRequest;
use App\HTTP\Requests\Recipient\ADSRecipientRequest;
use App\HTTP\Requests\Recipient\BulkStoreRecipientRequest;
use App\Models\Recipient;
use App\Models\AddressBook;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use App\Services\RecipientService;

class RecipientController extends Controller
{
    public function __construct(protected RecipientService $recipientService) {}
    protected function findRecipientOrFail(string $id): Recipient
{
    return Recipient::findOrFail($id);
}
/*
    public function index(): Collection// вывод всех получателей
    {
        return Recipient::all();
    } 
*/
    public function store(StoreRecipientRequest $request, string $id): JsonResponse // создание получателя в аддресной книге
    {
        
        $recipient = $this->recipientService
            ->createRecipient($request, $id);
        if (!$recipient) {
            return response()->json(['message' => 'Recipient not created'], 400);
        }
        return response()->json($recipient, 201);
    }

    public function detach(Request $request, string $idAddressBook, string $idRecipient): JsonResponse// отвязка получателя от адресной книги
    {
        $detachRecipient = $this->recipientService->detachRecipient(request: $request, idAddressBook: $idAddressBook, idRecipient: $idRecipient);
        if ($detachRecipient->getStatusCode() !== 200) {
            return $detachRecipient;
        }
        return response()->json(['message' => 'Recipient detached from address book'], 200); 
    }

    public function bulkStore(BulkStoreRecipientRequest $request): JsonResponse // массовое создание получателей
    {
        $validated = $request->validated();

        $recipients = collect($validated['recipients'])->map(function ($recipient) {
            return array_merge($recipient, [
                'id' => (string) Str::uuid()
            ]);
        });

        Recipient::insert($recipients->toArray());

        return response()->json([
            'message' => 'Recipients created',
        ], 201);
    }

    public function show(Request $request, string $id): JsonResponse // вывод всех получателей адресной книги
    {
        $clientKey = $request->header('X-Client-Key');
        if(!$id){
            return response()->json(['message' => 'No IDs provided for deletion'], 400);        
        }else{
        $addressBook = AddressBook::where('client_key', $clientKey)
        ->where('id', $id)->first();
        }
        if (!$addressBook) {
            return response()->json(['message' => 'Address book not found'], 404);
        }else{
        return response()->json([
            'recipients' => $addressBook->recipients,
        ]);}
    }
    public function update(UpdateRecipientRequest $request, string $id): Recipient // update
    {

        $validated = $request->validated();
        $recipient = $this->findRecipientOrFail($id);

        $recipient->update($validated);

        return $recipient;
    }

    public function attach(ADSRecipientRequest $request, Recipient $recipient): JsonResponse // привязка адресных книг к получателю
    {
        $validated = $request->validated();
        $addressBookIds = $validated['address_book_ids'];
        $recipient->addressBooks()->syncWithoutDetaching($addressBookIds);
        if ($recipient->trashed()) {
            $recipient->restore();
        }
        return response()->json(['message' => 'Address books attached']);
    }

    public function detachRecipient(ADSRecipientRequest $request, Recipient $recipient): JsonResponse // отвязка адресных книг от получателя
    {
        $validated = $request->validated();
        $addressBookIds = $validated['address_book_ids'];

        $recipient->addressBooks()->detach($addressBookIds);

        return response()->json(['message' => 'Address books detached']);
    }

    public function sync(ADSRecipientRequest $request, Recipient $recipient): JsonResponse // синхронизация адресных книг с получателем
    {
        $validated = $request->validated();
        $addressBookIds = $validated['address_book_ids'];

        $recipient->addressBooks()->sync($addressBookIds);

        return response()->json(['message' => 'Address books synced']);
    }

}

