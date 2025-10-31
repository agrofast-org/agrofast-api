<?php

namespace App\Http\Controllers;

use App\Factories\DocumentFactory;
use App\Http\Requests\Document\DocumentStoreRequest;
use App\Http\Requests\Document\DocumentUpdateRequest;
use App\Models\Hr\Document;
use App\Models\Hr\User;
use App\Services\UserDocumentService;

class DocumentController extends Controller
{
    protected $userDocumentService;

    public function __construct(
        UserDocumentService $userDocumentService
    ) {
        $this->userDocumentService = $userDocumentService;
    }

    public function index()
    {
        $user = User::auth()->load('documents');

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json($user->documents, 200);
    }

    public function store(DocumentStoreRequest $request)
    {
        $data = $request->validated();

        $document = DocumentFactory::create(
            User::auth(),
            $data
        );

        return response()->json($document, 201);
    }

    public function show($uuid)
    {
        $user = User::auth();

        $document = Document::where(['uuid' => $uuid, 'user_id' => $user->id])->first();

        if (!$document) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        return response()->json($document, 200);
    }

    public function update($uuid, DocumentUpdateRequest $request)
    {
        $user = User::auth();
        $data = $request->validated();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $document = Document::where(['uuid' => $uuid, 'user_id' => $user->id])->first();

        if (!$document) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        $document->update($data);

        return response()->json($document, 200);
    }

    public function delete($uuid)
    {
        $user = User::auth();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $document = Document::where(['uuid' => $uuid, 'user_id' => $user->id])->first();

        if (!$document) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        $document->update(['active' => false, 'inactivated_at' => now()]);

        return response()->json(['message' => 'Document deleted successfully'], 200);
    }
}
