<?php

namespace App\Services;

use App\Models\Error;
use App\Models\File\FilesImage;
use App\Models\Hr\User;
use App\Models\Success;
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
    public function getPicture(int $userId, ?string $pictureUuid = null): Error|Success
    {
        $files = Storage::files("uploads/pictures/{$userId}");
        if (empty($files)) {
            return new Error('no_images_found');
        }

        if ($pictureUuid) {
            $filePath = "uploads/pictures/{$userId}/{$pictureUuid}";
            if (!Storage::exists($filePath)) {
                return new Error('no_images_found');
            }
            $file = Storage::get($filePath);
            $type = Storage::mimeType($filePath);

            return new Success('image_found', ['file' => $file, 'mime' => $type]);
        }

        $lastFile = end($files);
        $file = Storage::get($lastFile);
        $type = Storage::mimeType($lastFile);

        return new Success('image_found', ['file' => $file, 'mime' => $type]);
    }

    /**
     * Uploads an image and updates the user's record.
     *
     * @return array Result with the file record or error
     */
    public function uploadPicture(Request $request, User $user): Error|Success
    {
        $validated = $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $file = $validated['image'];
        $fileName = Str::uuid().'.'.$file->getClientOriginalExtension();
        $disk = env('FILESYSTEM_DISK', 's3');

        $path = $file->storeAs("uploads/pictures/{$user->id}", $fileName, $disk);
        if (!$path) {
            return new Error('failed_to_upload_image');
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

            return new Error('failed_to_save_image_record');
        }

        return new Success('image_found', ['file' => $fileRecord]);
    }
}
