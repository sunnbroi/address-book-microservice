<?php

namespace App\Http\Controllers;

use App\Http\Requests\Recipient\DestroyRecipientRequest;
use App\Http\Requests\Recipient\StoreRecipientRequest;
use App\Http\Requests\Recipient\UpdateRecipientRequest;
use App\Http\Requests\Recipient\ADSRecipientRequest;
use App\Http\Requests\Recipient\BulkStoreRecipientRequest;
use App\Models\Recipient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\RecipientService;

class RecipientController extends Controller
{
    public function __construct(protected RecipientService $recipientService) {}

    public function show(Request $request, string $id): JsonResponse
    {
        $clientKey = $request->header('X-Client-Key');
        $recipients = $this->recipientService->getRecipientsByAddressBook($clientKey, $id);

        if (is_null($recipients)) {
            return response()->json(['message' => 'Address book not found'], 404);
        }
        return response()->json(['recipients' => $recipients]);
    }

    public function store(StoreRecipientRequest $request, string $id): JsonResponse
    {
        $clientKey = $request->header('X-Client-Key');
        $data = $request->validated();

        $recipient = $this->recipientService->createRecipient($data, $clientKey, $id);

        if (!$recipient) {
            return response()->json(['message' => 'Address book not found or recipient not created'], 404);
        }

        return response()->json($recipient, 201);
    }

    public function destroy(DestroyRecipientRequest $request): JsonResponse
    {
        $recipientIds = $request->validated()['recipient_ids'];

        if (empty($recipientIds)) {
            return response()->json(['message' => 'No recipient IDs provided for deletion'], 400);
        }

        Recipient::whereIn('id', $recipientIds)->delete();

        return response()->json(['message' => 'Recipients deleted successfully'], 200);
    }

    public function delete(Request $request, string $addressBookId, string $recipientId): JsonResponse
    {
        $clientKey = $request->header('X-Client-Key');

        $success = $this->recipientService->deleteRecipient($clientKey, $addressBookId, $recipientId);

        if (!$success) {
            return response()->json(['message' => 'Not found or access denied'], 404);
        }

        return response()->json(['message' => 'Recipient detached and deleted'], 200);
    }

    public function bulkStore(BulkStoreRecipientRequest $request): JsonResponse
    {
        $data = $request->validated();
        $this->recipientService->bulkStoreRecipients($data['recipients']);

        return response()->json(['message' => 'Recipients created'], 201);
    }

    public function update(UpdateRecipientRequest $request, string $id): JsonResponse
    {
        $data = $request->validated();
        $recipient = Recipient::findOrFail($id);

        $updated = $this->recipientService->updateRecipient($recipient, $data);

        return response()->json($updated);
    }

    public function attach(ADSRecipientRequest $request, Recipient $recipient): JsonResponse
    {
        $ids = $request->validated()['address_book_ids'];
        $this->recipientService->attachAddressBooks($recipient, $ids);

        return response()->json(['message' => 'Address books attached']);
    }

    public function detachRecipient(ADSRecipientRequest $request, Recipient $recipient): JsonResponse
    {
        $ids = $request->validated()['address_book_ids'];
        $this->recipientService->detachAddressBooks($recipient, $ids);

        return response()->json(['message' => 'Address books detached']);
    }

    public function sync(ADSRecipientRequest $request, Recipient $recipient): JsonResponse
    {
        $ids = $request->validated()['address_book_ids'];
        $this->recipientService->syncAddressBooks($recipient, $ids);

        return response()->json(['message' => 'Address books synced']);
    }
}
