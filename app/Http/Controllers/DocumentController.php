<?php

namespace App\Http\Controllers;

use App\Factories\ResponseFactory;
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

    public function delete($uuid)
    {
        $user = User::auth();

        if (!$user) {
            return ResponseFactory::error('user_not_found', null, 404);
        }

        $document = Document::where(['uuid' => $uuid, 'user_id' => $user->id])->first();

        if (!$document) {
            return ResponseFactory::error('document_not_found', null, 404);
        }

        $document->update(['active' => false, 'inactivated_at' => now()]);

        return ResponseFactory::success('document_deleted');
    }
}
