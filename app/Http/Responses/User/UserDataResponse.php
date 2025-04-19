<?php

namespace App\Http\Responses\User;

use App\Models\Hr\Document;
use App\Models\Hr\User;

class UserDataResponse
{
    /**
     * Format the user data for the response.
     */
    public static function format(User $user): array
    {
        return [
            'id' => $user->id,
            'uuid' => $user->uuid,
            'name' => $user->name,
            'surname' => $user->surname,
            'email' => $user->email,
            'number' => $user->number,
            'profile_picture' => $user->profile_picture,
        ];
    }

    /**
     * Format the user data for the response with document.
     *
     * @param User[] $users
     */
    public static function list(array $users): array
    {
        $items = [];
        foreach ($users as $user) {
            $items[] = self::format($user);
        }

        return $items;
    }

    public static function withDocument(User $user): array
    {
        $user = self::format($user);
        $documents = Document::where('user_id', $user['id'])->get();

        return [
            'user' => $user,
            'documents' => UserDocumentDataResponse::list($documents->all()),
        ];
    }
}
