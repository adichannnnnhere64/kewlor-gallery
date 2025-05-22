<?php

namespace App\Jobs;

use App\Models\Media;
use App\Models\MediaGroup;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Image;
use Intervention\Image\Typography\FontFactory;
use Plank\Mediable\Facades\ImageManipulator;
use Plank\Mediable\Facades\MediaUploader;
use Plank\Mediable\ImageManipulation;

class ProcessAudioUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use Batchable;

    public function __construct(
        protected Model $model,
        protected string $tempFilePath
    ) {}

    public function handle(): void
    {
        DB::transaction(function () {

            $media = MediaUploader::fromSource($this->tempFilePath)
                ->useHashForFilename('sha1')
                ->toDestination('public', 'audio')
                ->upload();

            $this->model->attachMedia($media, ['default']);
            $media->media_groups()->sync([
                MediaGroup::first()->id
            ]);

        });
    }

}
