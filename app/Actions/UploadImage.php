<?php

namespace App\Actions;

use App\Models\LiveEventGallery;
use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
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
