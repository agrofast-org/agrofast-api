<?php

namespace App\Services;

use AWS\CRT\HTTP\Request;

class UserDocumentService
{
    public function handleList(Request $request, array $documents): array
    {
        $items = [];
        foreach ($documents as $document) {
            $items[] = [
                'id' => $document->id,
                'uuid' => $document->uuid,
                'type' => $document->type,
                'number' => $document->number,
            ];
        }

        return $items;
    }
}
