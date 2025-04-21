<?php

namespace App\Factories;

use App\Models\Hr\Document;
use App\Models\Hr\User;
use Illuminate\Support\Str;

class DocumentFactory
{
    public static function create(User $user, array $document): Document
    {
        $registeredDocument = Document::where('number', $document['number'])->first();

        if ($registeredDocument) {
            $registeredDocument->active = true;
            $registeredDocument->update();

            return $registeredDocument;
        }

        return Document::create([
            'uuid' => Str::uuid(),
            'user_id' => $user->id,
            'emission_date' => $document['emission_date'],
            'document_type' => $document['document_type'],
            'number' => $document['number'],
        ]);
    }
}
