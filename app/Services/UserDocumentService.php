<?php

namespace App\Services;

use AWS\CRT\HTTP\Request;

class UserDocumentService
{
    public function createDocument() {}

    public function handleList(Request $request, array $documents): array
    {
        $items = [];
        foreach ($documents as $document) {
            $items[] = [
                'uuid' => $document->uuid,
                'type' => $document->type,
                'number' => $document->number,
            ];
        }

        return $items;
    }
}
