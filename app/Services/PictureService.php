<?php

namespace App\Services;

use App\Models\File\FilesImage;
use App\Models\Hr\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PictureService
{
    /**
     * Returns the user's image from Storage.
     *
     * @return array ['file' => content, 'mime' => type] or error
     */
    public function getPicture(int $userId, ?string $pictureUuid = null): array
    {
        $files = Storage::files("uploads/pictures/{$userId}");
        if (empty($files)) {
            return ['error' => 'no_images_found'];
        }

        if ($pictureUuid) {
            $filePath = "uploads/pictures/{$userId}/{$pictureUuid}";
            if (!Storage::exists($filePath)) {
                return ['error' => 'image_not_found'];
            }
            $file = Storage::get($filePath);
            $type = Storage::mimeType($filePath);

            return ['file' => $file, 'mime' => $type];
        }

        $lastFile = end($files);
        $file = Storage::get($lastFile);
        $type = Storage::mimeType($lastFile);

        return ['file' => $file, 'mime' => $type];
    }

    /**
     * Uploads an image and updates the user's record.
     *
     * @return array Result with the file record or error
     */
    public function uploadPicture(Request $request, User $user): array
    {
        $validated = $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $file = $validated['image'];
        $fileName = Str::uuid().'.'.$file->getClientOriginalExtension();
        $disk = env('FILESYSTEM_DISK', 's3');

        $path = $file->storeAs("uploads/pictures/{$user->id}", $fileName, $disk);
        if (!$path) {
            return ['error' => 'failed_to_upload_image'];
        }

        $fileRecord = FilesImage::create([
            'id' => Str::uuid(),
            'name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'uploaded_by' => $user->id,
        ]);

        $appUrl = env('APP_URL');
        $user->update([
            'profile_picture' => "{$appUrl}uploads/pictures/{$path}",
        ]);

        if (!$fileRecord) {
            Storage::disk($disk)->delete($path);

            return ['error' => 'failed_to_save_image_record'];
        }

        return ['file' => $fileRecord];
    }
}
