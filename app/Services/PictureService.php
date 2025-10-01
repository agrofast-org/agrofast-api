<?php

namespace App\Services;

use App\Http\Responses\User\UserDataResponse;
use App\Models\Error;
use App\Models\File\File;
use App\Models\Hr\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PictureService
{
    /**
     * Returns the user's image from Storage.
     */
    public function getPicture(string $userUuid, ?string $pictureUuid = null)
    {
        $filePath = $pictureUuid
            ? "uploads/pictures/{$userUuid}/{$pictureUuid}"
            : $this->getLastUploadedPicturePath($userUuid);

        if (!$filePath || !Storage::exists($filePath)) {
            return false;
        }

        return [
            'file' => Storage::get($filePath),
            'mime' => Storage::mimeType($filePath),
        ];
    }

    /**
     * Uploads an image and updates the user's record.
     *
     * @return array Result with the file record or error
     */
    public function uploadPicture(Request $request, User $user)
    {
        $validated = $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $uuid = Str::uuid();

        $file = $validated['image'];
        $fileName = $uuid.'.'.$file->getClientOriginalExtension();
        $disk = env('FILESYSTEM_DISK', 's3');

        $path = $file->storeAs("uploads/pictures/{$user->uuid}", $fileName, $disk);
        if (!$path) {
            return throw new \Exception('failed_to_store_image');
        }

        $fileRecord = File::create([
            'uuid' => $uuid,
            'name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'uploaded_by' => $user->id,
        ]);

        $appUrl = env('APP_URL');
        $user->update([
            'profile_picture' => "{$appUrl}/{$path}",
        ]);

        if (!$fileRecord) {
            Storage::disk($disk)->delete($path);

            return throw new \Exception('failed_to_save_image_record');
        }

        return ['user' => UserDataResponse::format($user)];
    }

    /**
     * Retrieves the path of the last uploaded picture for a user.
     */
    private function getLastUploadedPicturePath(string $userUuid): ?string
    {
        $files = Storage::files("uploads/pictures/{$userUuid}");

        return empty($files) ? null : end($files);
    }
}
