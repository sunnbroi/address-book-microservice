<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressBook\ADSAddressBookRequest;
use App\Http\Requests\AddressBook\StoreAddressBookRequest;
use App\Http\Requests\AddressBook\UpdateAddressBookRequest;
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
        if($request->input('client_key') == null) {
            return response()->json(['message' => 'Client key is required'], 400);
        }
        return AddressBook::where('client_key', $request->input('client_key'))->get();
    } 

    public function store(StoreAddressBookRequest $request): AddressBook // create
    {
        return AddressBook::create([
            'id' => Str::uuid(),
            'client_key' => $request->input('client_key'), // из middleware
            'name' =>  $request->input('name'),
        ]);
    }

    public function show(Request $request, string $id): AddressBook // read
    {
        return $this->findAddressBookOrFail($id, $request->input('client_key'));
    }

    public function update(UpdateAddressBookRequest $request, string $id): AddressBook // update
    {

        $book = $this->findAddressBookOrFail($id, $request->input('client_key'));

        $book->update([
            'name' =>$request->input('name'),
        ]);

        return $book;
    }

    public function destroy(Request $request, string $id): JsonResponse    // delete
    {
        $book = $this->findAddressBookOrFail($id, $request->input('client_key'));

        $book->delete();

        return response()->json(['message' => 'Deleted']);
    }

    public function attach(ADSAddressBookRequest $request, AddressBook $addressBook): JsonResponse // привязка получателей к адресной книге
    {
        $recipientIds = $request->input('recipient_ids');

        $addressBook->recipients()->syncWithoutDetaching($recipientIds);

        return response()->json(['message' => 'Recipients attached']);
    }

    public function detach(ADSAddressBookRequest $request, AddressBook $addressBook): JsonResponse // отвязка получателей от адресной книги
    {
        $recipientIds = $request->input('recipient_ids');

        $addressBook->recipients()->detach($request->input( $recipientIds));

        return response()->json(['message' => 'Recipients detached']);
    }

    public function sync(ADSAddressBookRequest $request, AddressBook $addressBook): JsonResponse // замена получателей в адресной книге
    {
        $recipientIds = $request->input('recipient_ids');

        $addressBook->recipients()->sync($request->input($recipientIds));

        return response()->json(['message' => 'Recipients synced']);
    }
    
}
