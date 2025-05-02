<?php

namespace App\Actions;

use App\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Image;
use Intervention\Image\Typography\FontFactory;
use Plank\Mediable\Facades\ImageManipulator;
use Plank\Mediable\Facades\MediaUploader;
use Plank\Mediable\ImageManipulation;

final class UploadImage
{
    public function handle(Model $model, UploadedFile $file): void
    {
        DB::transaction(function () use ($model, $file) {

            $manipulation = ImageManipulation::make(function (Image $image, Media $originalMedia) {
                // Initial image processing
                $image->sharpen(5);
       //         $originalWidth = $image->width();
      //          $originalHeight = $image->height();

     //           $largeImageThreshold = 1300;
    //            $maxImageSize = 1200;

                // THIS IS USEFUL
                /* if ($originalWidth > $largeImageThreshold || $originalHeight > $largeImageThreshold) { */
                /*     $ratio = min( */
                /*         $maxImageSize / $originalWidth, */
                /*         $maxImageSize / $originalHeight */
                /*     ); */
                /*     $image->resize( */
                /*         (int) ($originalWidth * $ratio), */
                /*         (int) ($originalHeight * $ratio), */
                /*         function ($constraint) { */
                /*             $constraint->aspectRatio(); */
                /*             $constraint->upsize(); */
                /*         } */
                /*     ); */
                /*     $image->sharpen(5); */
                /* } */
                // THIS IS USEFUL WHEN YOU WANT TO CHANGE THE SHIT

                $text = setting('watermark') ?? config('app.name');

                $watermarkColor = 'rgba(255, 255, 255, 0.5)';

                $fontSize = min(
                    100,
                    max(
                        20,
                        $image->width() * 0.03
                    )
                );

                // Calculate padding (2% of image width)
                $paddingX = $image->width() * (float) setting('xaxis') ?? 0.02;
                $paddingY = $image->width() * (float) setting('yaxis') ?? 0.02;

                // Position at bottom right
                $x = $image->width() - $paddingX;
                $y = $image->height() - $paddingY;

                $image->text($text, $x, $y, function (FontFactory $font) use ($fontSize, $watermarkColor) {
                    $font->size($fontSize);
                    $font->color($watermarkColor);
                    $font->file(public_path('roboto.ttf'));
                    $font->align('right');
                    $font->valign('bottom');
                    $font->angle(0);
                });

            });

            $media = MediaUploader::fromSource($file)
                ->useHashForFilename('sha1')
//                ->applyImageManipulation($manipulation)
                ->toDestination('public', 'gallery')
                ->upload();

            $model->attachMedia($media, ['default']);

            ImageManipulator::createImageVariant($media, 'thumbnail');
            $model->attachMedia($media, ['thumbnail']);

        });
    }
}
