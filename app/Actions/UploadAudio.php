<?php

namespace App\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Plank\Mediable\Facades\MediaUploader;

final class UploadAudio
{
    public function handle(Model $model, UploadedFile $file): void
    {
        DB::transaction(function () use ($model, $file) {

            $media = MediaUploader::fromSource($file)
                ->useHashForFilename('sha1')
                ->toDestination('public', 'audio')
                ->upload();

            $model->attachMedia($media, ['default']);

        });
    }
}

