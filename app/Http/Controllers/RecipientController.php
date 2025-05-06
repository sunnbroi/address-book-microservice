<?php

namespace App\Http\Controllers;

use App\HTTP\Requests\Recipient\StoreRecipientRequest;
use App\HTTP\Requests\Recipient\UpdateRecipientRequest;
use App\HTTP\Requests\Recipient\ADSRecipientRequest;
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
    public function store(StoreRecipientRequest $request): Recipient// create
    {
        return Recipient::create(array_merge($request->validated(), [
            'id' => Str::uuid()
        ]));
    }
    public function show(string $id): Recipient   // read
    {
       return $this->findRecipientOrFail($id);
    }
    public function update(UpdateRecipientRequest $request, string $id): Recipient // update
    {
        $recipient = $this->findRecipientOrFail($id);

        $recipient->update($request->validated());

        return $recipient;
    }

    public function destroy(string $id): JsonResponse// delete
    {
        $this->findRecipientOrFail($id)->delete();

        return response()->json(['message' => 'Deleted']);
    }

    public function attach(ADSRecipientRequest $request, Recipient $recipient): JsonResponse // привязка адресных книг к получателю
    {
        $addressBookIds = $request->input('address_book_ids');

        $recipient->addressBooks()->syncWithoutDetaching($addressBookIds);

        return response()->json(['message' => 'Address books attached']);
    }

    public function detach(ADSRecipientRequest $request, Recipient $recipient): JsonResponse // отвязка адресных книг от получателя
    {
        $addressBookIds = $request->input('address_book_ids');

        $recipient->addressBooks()->detach($addressBookIds);

        return response()->json(['message' => 'Address books detached']);
    }

    public function sync(ADSRecipientRequest $request, Recipient $recipient): JsonResponse // синхронизация адресных книг с получателем
    {
        $addressBookIds = $request->input('address_book_ids');

        $recipient->addressBooks()->sync($addressBookIds);

        return response()->json(['message' => 'Address books synced']);
    }

}

