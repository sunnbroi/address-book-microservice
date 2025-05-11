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
    public function __construct(RecipientService $recipientService) {}
    protected function findRecipientOrFail(string $id): Recipient
{
    return Recipient::findOrFail($id);
}
    public function index(): Collection// вывод всех получателей
    {
        return Recipient::all();
    }
    public function store(StoreRecipientRequest $request, string $id): Recipient // create
    {
        $clientKey = $request->header('X-Client-Key');
        $validated = $request->validated();
        $addressBook = AddressBook::where('i', $id)
        ->where('client_key', $clientKey)->first();
        if (!$addressBook) {
            return response()->json(['message' => 'Address book not found'], 404);
        }
        $data = [
            'id' => (string) Str::uuid(),
            'telegram_user_id' => $validated['telegram_user_id'],
            'address_book_id' => $addressBook->id,
        ];

        if ($validated->has['username']) {
            $data['username'] = $validated['username'];
        } elseif($validated->has['first_name']) {
            $data['first_name'] = $validated['first_name'];
        } elseif($validated->has['last_name']) {
            $data['last_name'] = $validated['last_name'];
        } elseif($validated->has['type']) {
            $data['type'] = $validated['type'];
        }
        
        $recipient = Recipient::create($data);
        return response()->json($recipient, 201);
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
        $addressBook = AddressBook::where('recipient_id', $id)->where('client_key', $clientKey)->first();
        }
        return response()->json([
            'recipients' => $addressBook->recipients,
        ]);
    }
    public function update(UpdateRecipientRequest $request, string $id): Recipient // update
    {

        $validated = $request->validated();
        $recipient = $this->findRecipientOrFail($id);

        $recipient->update($validated);

        return $recipient;
    }

    public function destroy(Request $request, string $id): JsonResponse// delete
    {
        if (empty($id)) {
            return response()->json(['message' => 'No IDs provided for deletion'], 400);
        }

        Recipient::find($id)->delete();

        return response()->json(['message' => 'Recipients deleted']);
    }

    public function attach(ADSRecipientRequest $request, Recipient $recipient): JsonResponse // привязка адресных книг к получателю
    {
        $validated = $request->validated();
        $addressBookIds = $validated['address_book_ids'];

        $recipient->addressBooks()->syncWithoutDetaching($addressBookIds);

        return response()->json(['message' => 'Address books attached']);
    }

    public function detach(ADSRecipientRequest $request, Recipient $recipient): JsonResponse // отвязка адресных книг от получателя
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

