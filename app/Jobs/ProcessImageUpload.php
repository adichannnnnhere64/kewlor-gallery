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
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Image;
use Intervention\Image\Typography\FontFactory;
use Plank\Mediable\Facades\ImageManipulator;
use Plank\Mediable\Facades\MediaUploader;
use Plank\Mediable\ImageManipulation;

class ProcessImageUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use Batchable;

    public function __construct(
        protected Model $model,
        protected string $tempFilePath
    ) {}

    public function handle(): void
    {
        $manipulation = ImageManipulation::make(function (Image $image, Media $originalMedia) {
            $image->sharpen(5);

            $text = setting('watermark') ?? config('app.name');
            $watermarkColor = 'rgba(255, 255, 255, 0.5)';

            $fontSize = min(
                100,
                max(
                    20,
                    $image->width() * 0.03
                )
            );

            $paddingX = $image->width() * (float) setting('xaxis') ?? 0.02;
            $paddingY = $image->width() * (float) setting('yaxis') ?? 0.02;

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

        // Get the full path to the temporary file
        $fullTempPath = Storage::path($this->tempFilePath);

        $media = MediaUploader::fromSource($fullTempPath)
            ->useHashForFilename('sha1')
            ->toDestination('public', 'gallery')
            ->upload();

        $this->model->attachMedia($media, ['default']);

        $media->media_groups()->sync([
            MediaGroup::first()->id
        ]);

        // Create variants
        ImageManipulator::createImageVariant($media, 'thumbnail');
        $this->model->attachMedia($media, ['thumbnail']);

        ImageManipulator::createImageVariant($media, 'preview');
        $this->model->attachMedia($media, ['preview']);

        // Clean up
        Storage::delete($this->tempFilePath);
    }

    public function failed(\Throwable $exception): void
    {
        // Clean up if job fails
        if (isset($this->tempFilePath) && Storage::exists($this->tempFilePath)) {
            Storage::delete($this->tempFilePath);
        }
    }
}
