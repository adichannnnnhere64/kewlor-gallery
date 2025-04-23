<?php

namespace App\Http\Controllers;

use App\Actions\UploadImage;
use App\Actions\UploadVideo;
use App\Http\Requests\UploadRequest;
use App\Models\LiveEventGallery;

class UploadController extends Controller
{
    public function store(
        UploadRequest $request,
        LiveEventGallery $model,
        UploadImage $uploadImage,
        UploadVideo $uploadVideo
    ) {
        $file = $request->file('file');

        if (str_starts_with($file->getMimeType(), 'image/')) {
            $uploadImage->handle($model, $file);
        } elseif (str_starts_with($file->getMimeType(), 'video/')) {
            $uploadVideo->handle($model, $file);
        } else {
            return response()->json([
                'message' => 'Unsupported file type',
            ], 422);
        }

        return response()->json([
            'message' => 'Upload successful',
        ]);
    }
}
