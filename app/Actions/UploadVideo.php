<?php

namespace App\Actions;

use App\Models\LiveEventGallery;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Plank\Mediable\Facades\MediaUploader;

final class UploadVideo
{
    public function handle(LiveEventGallery $model, UploadedFile $file): void
    {
        DB::transaction(function () use ($model, $file) {
            $media = MediaUploader::fromSource($file)
                ->useHashForFilename('sha1')
                ->toDestination('public', 'videos') // Store in public/videos
                ->upload();

            $model->attachMedia($media, ['default']);

        });
    }
}

