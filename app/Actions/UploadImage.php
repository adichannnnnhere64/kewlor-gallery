<?php

namespace App\Actions;

use App\Models\LiveEventGallery;
use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Image;
use Plank\Mediable\Facades\ImageManipulator;
use Plank\Mediable\Facades\MediaUploader;
use Plank\Mediable\ImageManipulation;

final class UploadImage
{
    public function handle(LiveEventGallery $model, UploadedFile $file): void
    {
        DB::transaction(function () use ($model, $file) {

            $manipulation = ImageManipulation::make(function (Image $image, Media $originalMedia) {
                $image->sharpen(5);
                $originalWidth = $image->width();
                $originalHeight = $image->height();

                $largeImageThreshold = 1300;
                $maxImageSize = 1200;

                if ($originalWidth > $largeImageThreshold || $originalHeight > $largeImageThreshold) {
                    $ratio = min(
                        $maxImageSize / $originalWidth,
                        $maxImageSize / $originalHeight
                    );
                    $image->resize(
                        (int) ($originalWidth * $ratio),
                        (int) ($originalHeight * $ratio),
                        function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        }
                    );
                    $image->sharpen(5);
                }

            })->setOutputQuality(75)->outputWebpFormat();

            $media = MediaUploader::fromSource($file)
                ->useHashForFilename('sha1')
                ->applyImageManipulation($manipulation)
                ->toDestination('public', 'gallery')
                ->upload();

            $model->attachMedia($media, ['default']);
            ImageManipulator::createImageVariant($media, 'thumbnail');
            $model->attachMedia($media, ['thumbnail']);

        });
    }
}
