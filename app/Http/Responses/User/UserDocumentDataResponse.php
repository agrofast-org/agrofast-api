<?php

namespace App\Http\Responses\User;

use App\Models\Hr\Document;

class UserDocumentDataResponse
{
    /**
     * Format the user data for the response.
     */
    public static function format(Document $user): array
    {
        return [
            'id' => $user->id,
            'uuid' => $user->uuid,
            'type' => $user->type,
            'number' => $user->number,
        ];
    }

    /**
     * Format the user data for the response with document.
     *
     * @param Document[] $documents
     */
    public static function list(array $documents): array
    {
        $items = [];
        foreach ($documents as $document) {
            $items[] = self::format($document);
        }

        return $items;
    }
}
