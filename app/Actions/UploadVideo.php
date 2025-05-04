<?php

namespace App\Actions;

use App\Models\LiveEventGallery;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Plank\Mediable\Facades\MediaUploader;

final class UploadVideo
{
    public function handle(LiveEventGallery $model, UploadedFile $file): void
    {
        DB::transaction(function () use ($model, $file) {
            // Save original uploaded video temporarily
            $tempPath = storage_path('app/tmp/' . uniqid('video_') . '.' . $file->getClientOriginalExtension());
            $file->move(dirname($tempPath), basename($tempPath));

            // Prepare watermarked video output path
            $watermarkedPath = storage_path('app/tmp/' . uniqid('watermarked_') . '.mp4');

            $text = addslashes(setting('watermark') ?? config('app.name'));
            $xaxis = setting('xaxis') ?? 0.02;
            $yaxis = setting('yaxis') ?? 0.02;

            // FFmpeg watermark command
            $ffmpegWatermarkCommand = sprintf(
                'ffmpeg -i %s -vf "drawtext=fontfile=%s:text=\'%s\':fontcolor=white@0.5:fontsize=24:x=w-(text_w*1.1)-%d:y=h-(text_h*1.1)-%d" -codec:a copy %s',
                escapeshellarg($tempPath),
                escapeshellarg(public_path('roboto.ttf')),
                $text,
                $xaxis * 100,
                $yaxis * 100,
                escapeshellarg($watermarkedPath)
            );

            shell_exec($ffmpegWatermarkCommand);

            $videoMedia = MediaUploader::fromSource($watermarkedPath)
                ->useHashForFilename('sha1')
                ->toDestination('public', 'videos')
                ->upload();

            $thumbnailPath = storage_path('app/tmp/' . uniqid('thumb_') . '.jpg');
            $ffmpegThumbnailCommand = sprintf(
                'ffmpeg -i %s -ss 00:00:01.000 -vframes 1 -vf "scale=500:500:force_original_aspect_ratio=increase,crop=500:500" -q:v 5 %s',
                escapeshellarg($watermarkedPath),
                escapeshellarg($thumbnailPath)
            );
            shell_exec($ffmpegThumbnailCommand);

            if (file_exists($thumbnailPath)) {
                $thumbnailMedia = MediaUploader::fromSource($thumbnailPath)
                    ->useFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '_thumb')
                    ->useHashForFilename('sha1')
                    ->toDestination('public', 'videos/thumbs')
                    ->upload();

                $videoMedia->setAttribute('video_thumbnail_id', $thumbnailMedia->id);
                $videoMedia->save();

                unlink($thumbnailPath);
            }

            unlink($tempPath);
            unlink($watermarkedPath);

            $model->attachMedia($videoMedia, ['default']);
        });
    }
}

