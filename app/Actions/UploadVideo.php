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

            // Upload the video directly without watermarking
            $videoMedia = MediaUploader::fromSource($tempPath)
                ->useHashForFilename('sha1')
                ->toDestination('public', 'videos')
                ->upload();

            // Still generate thumbnail from the original video
            $thumbnailPath = storage_path('app/tmp/' . uniqid('thumb_') . '.jpg');
            $ffmpegThumbnailCommand = sprintf(
                'ffmpeg -i %s -ss 00:00:01.000 -vframes 1 -vf "scale=500:500:force_original_aspect_ratio=increase,crop=500:500" -q:v 5 %s',
                escapeshellarg($tempPath),
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
            $model->attachMedia($videoMedia, ['default']);
        });
    }
}
