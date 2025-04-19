<?php

namespace App\Http\Controllers;

use App\Factories\ResponseFactory;
use App\Http\Requests\User\DocumentStoreRequest;
use App\Http\Responses\User\UserDataResponse;
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
        $user = User::auth();

        if (!$user) {
            return ResponseFactory::error('user_not_found', null, 404);
        }
        $documents = Document::where('user_id', $user['id'])->get();

        return ResponseFactory::success('user_found', $documents);
    }

    public function store(DocumentStoreRequest $request)
    {
        $user = User::auth();

        if (!$user) {
            return ResponseFactory::error('user_not_found', null, 404);
        }

        $data = $request->all();
        $result = $this->userDocumentService->createDocument($data, $user);

        if ($result instanceof Error) {
            return ResponseFactory::create($result);
        }

        return ResponseFactory::success('document_created', UserDataResponse::withDocument($result));
    }
}
