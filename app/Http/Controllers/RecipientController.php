<?php

namespace App\Http\Controllers;

use App\Http\Requests\Recipient\DestroyRecipientRequest;
use App\HTTP\Requests\Recipient\StoreRecipientRequest;
use App\HTTP\Requests\Recipient\UpdateRecipientRequest;
use App\HTTP\Requests\Recipient\ADSRecipientRequest;
use App\HTTP\Requests\Recipient\BulkStoreRecipientRequest;
use App\Models\Recipient;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;

class RecipientController extends Controller
{
    protected function findRecipientOrFail(string $id): Recipient
{
    return Recipient::findOrFail($id);
}
    public function index(): Collection// вывод всех получателей
    {
        return Recipient::all();
    }
    public function store(StoreRecipientRequest $request): Recipient // create
    {
        $validated = $request->validated();

        return Recipient::create(array_merge($validated, [
            'id' => Str::uuid()
        ]));
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

    public function show(string $id): Recipient   // read
    {
       return $this->findRecipientOrFail($id);
    }
    public function update(UpdateRecipientRequest $request, string $id): Recipient // update
    {

        $validated = $request->validated();
        $recipient = $this->findRecipientOrFail($id);

        $recipient->update($validated);

        return $recipient;
    }

    public function destroy(DestroyRecipientRequest $request): JsonResponse// delete
    {
        $validated = $request->validated();

        Recipient::whereIn('id', $validated['recipient_ids'])->delete();

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

