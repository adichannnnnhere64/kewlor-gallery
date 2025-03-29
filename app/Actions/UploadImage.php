<?php

namespace App\Actions;

use App\Models\LiveEventGallery;
use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Image;
use Intervention\Image\Interfaces\FontInterface;
use Intervention\Image\Typography\FontFactory;
use Plank\Mediable\Facades\ImageManipulator;
use Plank\Mediable\Facades\MediaUploader;
use Plank\Mediable\ImageManipulation;

final class UploadImage
{
    public function handle(LiveEventGallery $model, UploadedFile $file): void
    {
        DB::transaction(function () use ($model, $file) {



            $manipulation = ImageManipulation::make(function (Image $image, Media $originalMedia) {
    // Initial image processing
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

    $text = setting('watermark') ?? config('app.name');

    // Watermark configuration
    $watermarkColor = 'rgba(255, 255, 255, 0.5)'; // White with 70% opacity
    /* $shadowColor = 'rgba(0, 0, 0, 0.4)'; // Black with 50% opacity */
    /* $shadowOffset = 1; // Shadow offset in pixels */

    // Calculate font size (3% of image width, with min/max bounds)
    $fontSize = min(
        100, // Maximum font size
        max(
            20, // Minimum font size
            $image->width() * 0.03 // 3% of image width
        )
    );

    // Calculate padding (2% of image width)
    $paddingX = $image->width() * setting('xaxis') ?? 0.02;
    $paddingY = $image->width() * setting('yaxis') ?? 0.02;

    // Position at bottom right
    $x = $image->width() - $paddingX;
    $y = $image->height() - $paddingY;

    // First draw shadow (slightly offset)
    /* $image->text($text, $x + $shadowOffset, $y + $shadowOffset, function (FontFactory $font) use ($fontSize, $shadowColor) { */
    /*     $font->size($fontSize); */
    /*     $font->color($shadowColor); // Uses RGBA for opacity */
    /*     $font->file(public_path('roboto.ttf')); */
    /*     $font->align('right'); */
    /*     $font->valign('bottom'); */
    /*     $font->angle(0); */
    /* }); */

    // Then draw the main watermark text
    $image->text($text, $x, $y, function (FontFactory $font) use ($fontSize, $watermarkColor) {
        $font->size($fontSize);
        $font->color($watermarkColor); // Uses RGBA for opacity
        $font->file(public_path('roboto.ttf'));
        $font->align('right');
        $font->valign('bottom');
        $font->angle(0);
    });

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
