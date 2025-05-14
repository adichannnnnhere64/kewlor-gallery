<?php

namespace App\Jobs;

use App\Models\LiveEventGallery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Plank\Mediable\Facades\MediaUploader;

class ProcessVideoUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected LiveEventGallery $model,
        protected string $tempFilePath
    ) {}

    public function handle(): void
    {
        // Get the full path to the temporary file
        $fullTempPath = Storage::path($this->tempFilePath);

        $originalExtension = pathinfo($this->tempFilePath, PATHINFO_EXTENSION);
        $isMkv = strtolower($originalExtension) === 'mkv';

        $finalPath = $fullTempPath;

        if ($isMkv) {
            $convertedPath = storage_path('app/tmp/'.uniqid('converted_').'.mp4');
            $ffmpegConvertCommand = sprintf(
                'ffmpeg -i %s -c:v libx264 -preset fast -c:a aac -b:a 128k -movflags +faststart %s',
                escapeshellarg($fullTempPath),
                escapeshellarg($convertedPath)
            );
            shell_exec($ffmpegConvertCommand);

            if (file_exists($convertedPath)) {
                $finalPath = $convertedPath;
                Storage::delete($this->tempFilePath); // Remove original MKV
            } else {
                Storage::delete($this->tempFilePath);
                throw new \RuntimeException('Video conversion failed.');
            }
        }

        // Upload final video (original or converted)
        $videoMedia = MediaUploader::fromSource($finalPath)
            ->useHashForFilename('sha1')
            ->toDestination('public', 'videos')
            ->upload();

        // Generate thumbnail from the final video
        $thumbnailPath = storage_path('app/tmp/'.uniqid('thumb_').'.jpg');
        $ffmpegThumbnailCommand = sprintf(
            'ffmpeg -i %s -ss 00:00:01.000 -vframes 1 -vf "scale=500:500:force_original_aspect_ratio=increase,crop=500:500" -q:v 5 %s',
            escapeshellarg($finalPath),
            escapeshellarg($thumbnailPath)
        );
        shell_exec($ffmpegThumbnailCommand);

        if (file_exists($thumbnailPath)) {
            $thumbnailMedia = MediaUploader::fromSource($thumbnailPath)
                ->useFilename(pathinfo($finalPath, PATHINFO_FILENAME).'_thumb')
                ->useHashForFilename('sha1')
                ->toDestination('public', 'videos/thumbs')
                ->upload();

            $videoMedia->setAttribute('video_thumbnail_id', $thumbnailMedia->id);
            $videoMedia->save();
            unlink($thumbnailPath);
        }

        // Clean up
        if ($isMkv && file_exists($finalPath)) {
            unlink($finalPath); // Clean up converted MP4
        }

        $this->model->attachMedia($videoMedia, ['default']);

        // Delete the temporary file if it still exists
        if (Storage::exists($this->tempFilePath)) {
            Storage::delete($this->tempFilePath);
        }
    }

    public function failed(\Throwable $exception): void
    {
        // Clean up if job fails
        if (isset($this->tempFilePath) && Storage::exists($this->tempFilePath)) {
            Storage::delete($this->tempFilePath);
        }
    }
}
