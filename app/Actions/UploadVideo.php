<?php

namespace App\Actions;

use App\Models\LiveEventGallery;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Plank\Mediable\Facades\MediaUploader;

final class UploadVideo
{
    public function handle(LiveEventGallery $model, UploadedFile $file): void
    {
        DB::transaction(function () use ($model, $file) {
            $originalExtension = strtolower($file->getClientOriginalExtension());
            $isMkv = $originalExtension === 'mkv';

            // Save original uploaded file temporarily
            $tempPath = storage_path('app/tmp/'.uniqid('video_').'.'.$originalExtension);
            $file->move(dirname($tempPath), basename($tempPath));

            $finalPath = $tempPath;

            if ($isMkv) {
                $convertedPath = storage_path('app/tmp/'.uniqid('converted_').'.mp4');
                $ffmpegConvertCommand = sprintf(
                    'ffmpeg -i %s -c:v libx264 -preset fast -c:a aac -b:a 128k -movflags +faststart %s',
                    escapeshellarg($tempPath),
                    escapeshellarg($convertedPath)
                );
                shell_exec($ffmpegConvertCommand);

                if (file_exists($convertedPath)) {
                    $finalPath = $convertedPath;
                    unlink($tempPath); // Remove MKV after conversion
                } else {
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
                    ->useFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME).'_thumb')
                    ->useHashForFilename('sha1')
                    ->toDestination('public', 'videos/thumbs')
                    ->upload();

                $videoMedia->setAttribute('video_thumbnail_id', $thumbnailMedia->id);
                $videoMedia->save();
                unlink($thumbnailPath);
            }

            if (file_exists($finalPath)) {
                unlink($finalPath); // Clean up temp MP4 if converted
            }

            $model->attachMedia($videoMedia, ['default']);
        });
    }
}
