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

        $mediaItems = $model->media()->take(4)->get();

        $manager = new ImageManager(
            Driver::class
        );

        /** @var ImageManager $gridImage */
        $gridImage = $manager->create(800, 800);

        /** @var Media $mediaItem */
        foreach ($mediaItems as $index => $mediaItem) {
            $path = $mediaItem->getAbsolutePath(); // Get local path instead of URL

            if (file_exists($path)) {
                $image = $manager->read($path);
                $image->resize(400, 400);

                $x = ($index % 2) * 400;
                $y = floor($index / 2) * 400;

                $gridImage->insert($image, 'top-left', $x, $y);
            }
        }
        dd($gridImage);

        $media = MediaUploader::fromSource($file)
            ->toDestination('public', 'gallery/thumbnail')
            ->upload();
        $model->attachMedia($media, ['thumbnail']);

    }
}
