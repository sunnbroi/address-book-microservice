<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressBook\ADSAddressBookRequest;
use App\Http\Requests\AddressBook\StoreAddressBookRequest;
use App\Http\Requests\AddressBook\UpdateAddressBookRequest;
use App\Http\Requests\AddressBook\BulkStoreAddressBookRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\AddressBook;
use App\Services\AddressBookService;

class AddressBookController extends Controller
{
    public function __construct(protected AddressBookService $addressBookService) {}

    public function index(Request $request): JsonResponse
    {
        $clientKey = $request->header('X-Client-Key');
        if (!$clientKey) {
            return response()->json(['message' => 'Client key is required'], 400);
        }

        $books = $this->addressBookService->getClientBooks($clientKey);

        return response()->json($books);
    }

    public function store(StoreAddressBookRequest $request): JsonResponse
    {
        $clientKey = $request->header('X-Client-Key');
        $validated = $request->validated();
    
        // Попытка восстановить, если пришел address_book_id
        if (!empty($validated['address_book_id'])) {
            $restored = $this->addressBookService
                ->restoreAddressBookIfExists($validated['address_book_id'], $clientKey, $validated['name']);
        
            if ($restored) {
                return response()->json([
                    'message' => 'Address book restored',
                    'address_book' => $restored,
                ]);
            }
        
            return response()->json(['message' => 'Failed to restore address book'], 404);
        }
    
        // Создание новой
        $book = $this->addressBookService->createAddressBook($validated, $clientKey);
    
        return response()->json($book, 201);
    }


    public function show(Request $request, string $id): JsonResponse
    {
        $clientKey = $request->header('X-Client-Key');

        $book = $this->addressBookService->findAddressBook($id, $clientKey);

        if (!$book) {
            return response()->json(['message' => 'Address book not found'], 404);
        }

        return response()->json($book);
    }

    public function update(UpdateAddressBookRequest $request, string $id): JsonResponse
    {
        $clientKey = $request->header('X-Client-Key');
        $validated = $request->validated();

        $book = $this->addressBookService->findAddressBook($id, $clientKey);

        if (!$book) {
            return response()->json(['message' => 'Address book not found'], 404);
        }

        $updated = $this->addressBookService->updateAddressBook($book, $validated);

        return response()->json($updated);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $clientKey = $request->header('X-Client-Key');
        if (!$id) {
            return response()->json(['message' => 'No ID provided'], 400);
        }

        $book = $this->addressBookService->findAddressBook($id, $clientKey);

        if (!$book) {
            return response()->json(['message' => 'Address book not found'], 404);
        }

        $this->addressBookService->deleteAddressBook($book);

        return response()->json(['message' => 'Address book deleted']);
    }

    public function bulkStore(BulkStoreAddressBookRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $this->addressBookService->bulkStoreAddressBooks($validated['address_books']);

        return response()->json(['message' => 'Address books created'], 201);
    }

    public function attach(ADSAddressBookRequest $request, AddressBook $addressBook): JsonResponse
    {
        $recipientIds = $request->validated()['recipient_ids'];

        $this->addressBookService->attachRecipients($addressBook, $recipientIds);

        return response()->json(['message' => 'Recipients attached']);
    }

    public function detach(ADSAddressBookRequest $request, AddressBook $addressBook): JsonResponse
    {
        $recipientIds = $request->validated()['recipient_ids'];

        $this->addressBookService->detachRecipients($addressBook, $recipientIds);

        return response()->json(['message' => 'Recipients detached']);
    }

    public function sync(ADSAddressBookRequest $request, AddressBook $addressBook): JsonResponse
    {
        $recipientIds = $request->validated()['recipient_ids'];

        $this->addressBookService->syncRecipients($addressBook, $recipientIds);

        return response()->json(['message' => 'Recipients synced']);
    }
}
