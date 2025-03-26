<?php

namespace App\Actions;

use App\Models\LiveEventGallery;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Plank\Mediable\Facades\MediaUploader;

final class UploadImage
{
    public function handle(LiveEventGallery $model, UploadedFile $file): void
    {
        DB::transaction(function () use ($model, $file) {

            $media = MediaUploader::fromSource($file)
                ->toDestination('public', 'gallery')
                ->upload();

            $model->attachMedia($media, ['default']);

        });
    }
}
