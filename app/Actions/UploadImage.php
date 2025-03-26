<?php

namespace App\Actions;

use App\Models\LiveEventGallery;
use Illuminate\Http\UploadedFile;
use Plank\Mediable\Facades\MediaUploader;

final class UploadImage
{
    public function handle(LiveEventGallery $model, UploadedFile $file): void
    {
        $media = MediaUploader::fromSource($file)
            ->toDestination('public', 'gallery')
            ->upload();

        $model->attachMedia($media, ['default']);

        /* $media = MediaUploader::fromSource($file) */
        /*     ->toDestination('public', 'gallery/thumbnail') */
        /*     ->upload(); */
        /* $model->attachMedia($media, ['thumbnail']); */

    }
}
