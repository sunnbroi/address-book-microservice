<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressBook\ADSAddressBookRequest;
use App\Http\Requests\AddressBook\StoreAddressBookRequest;
use App\Http\Requests\AddressBook\UpdateAddressBookRequest;
use App\Http\Requests\AddressBook\BulkStoreAddressBookRequest;
use App\Http\Requests\AddressBook\DestroyAddressBookRequest;
use Illuminate\Http\Request;
use App\Models\AddressBook;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class AddressBookController extends Controller
{
    protected function findAddressBookOrFail(string $id, string $clientKey): AddressBook
    {
        return AddressBook::where('id', $id)
            ->where('client_key', $clientKey)
            ->firstOrFail();
    }

    public function index(Request $request) // вывод всех книг клиента
    {
        $validatedRequest = $request->validated();
        if (!isset($validatedRequest['client_key'])) {
            return response()->json(['message' => 'Client key is required'], 400);
        }
        return AddressBook::where('client_key', $validatedRequest['client_key'])->get();
    }

    public function store(StoreAddressBookRequest $request): AddressBook // create
    {
        $validatedRequest = $request->validated();
        return AddressBook::create([
            'id' => Str::uuid(),
            'client_key' => $validatedRequest['client_key'], // из middleware
            'name' => $validatedRequest['name'],
        ]);
    }

    public function show(Request $request, string $id): AddressBook // read
    {
        $validatedRequest = $request->validated();
        return $this->findAddressBookOrFail($id, $validatedRequest['client_key']);
    }

    public function update(UpdateAddressBookRequest $request, string $id): AddressBook // update
    {
        $validatedRequest = $request->validated();
        $book = $this->findAddressBookOrFail($id, $validatedRequest['client_key']);

        $book->update([
            'name' => $validatedRequest['name'],
        ]);

        return $book;
    }

    public function destroy(DestroyAddressBookRequest $request, string $id): JsonResponse // delete
    {
        $validatedRequest = $request->validated();
        $ids = $validatedRequest['address_book_ids'];
            if (empty($ids)) {
                return response()->json(['message' => 'No IDs provided for deletion'], 400);
            }

        AddressBook::whereIn('id', $ids['address_book_ids'])->delete();

        return response()->json(['message' => 'Address books deleted'], 200);
    }

    public function bulkStore(BulkStoreAddressBookRequest $request): JsonResponse // массовое создание получателей
    {
        $validatedRequest = $request->validated();

        AddressBook::insert($validatedRequest['address_books']);

        return response()->json(['message' => 'Address books created'], 201);
    }

    public function attach(ADSAddressBookRequest $request, AddressBook $addressBook): JsonResponse // привязка получателей к адресной книге
    {
        $validatedRequest = $request->validated();
        $recipientIds = $validatedRequest['recipient_ids'];

        $addressBook->recipients()->syncWithoutDetaching($recipientIds);

        return response()->json(['message' => 'Recipients attached']);
    }

    public function detach(ADSAddressBookRequest $request, AddressBook $addressBook): JsonResponse // отвязка получателей от адресной книги
    {
        $validatedRequest = $request->validated();
        $recipientIds = $validatedRequest['recipient_ids'];

        $addressBook->recipients()->detach($recipientIds);

        return response()->json(['message' => 'Recipients detached']);
    }

    public function sync(ADSAddressBookRequest $request, AddressBook $addressBook): JsonResponse // замена получателей в адресной книге
    {
        $validatedRequest = $request->validated();
        $recipientIds = $validatedRequest['recipient_ids'];

        $addressBook->recipients()->sync($recipientIds);

        return response()->json(['message' => 'Recipients synced']);
    }
}
