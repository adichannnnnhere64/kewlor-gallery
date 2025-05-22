<?php
namespace App\Jobs;
use App\Models\LiveEventGallery;
use App\Models\MediaGroup;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Plank\Mediable\Facades\MediaUploader;

class ProcessVideoUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use Batchable;

    // Set reasonable limits to prevent runaway processes
    public $timeout = 600;     // 10 minutes max execution time
    public $tries = 2;         // Only try twice
    public $maxExceptions = 1; // Only allow 1 exception before marking as failed

    public function __construct(
        protected LiveEventGallery $model,
        protected string $tempFilePath
    ) {}

    public function handle(): void
    {
        Log::info("Starting video processing for: " . $this->tempFilePath);

        try {
            // Check if file exists and is readable
            if (!Storage::exists($this->tempFilePath)) {
                throw new \RuntimeException('Video file not found or not readable: ' . $this->tempFilePath);
            }

            // Get file size and check if it's reasonable
            $fileSize = Storage::size($this->tempFilePath);
            if ($fileSize <= 0 || $fileSize > 500 * 1024 * 1024) { // Max 500MB
                throw new \RuntimeException('File is empty or too large: ' . $fileSize . ' bytes');
            }

            // Get the full path to the temporary file
            $fullTempPath = Storage::path($this->tempFilePath);
            $originalExtension = pathinfo($this->tempFilePath, PATHINFO_EXTENSION);
            $isMkv = strtolower($originalExtension) === 'mkv';
            $finalPath = $fullTempPath;

            if ($isMkv) {
                $convertedPath = storage_path('app/tmp/'.uniqid('converted_').'.mp4');

                // Add timeout protection and limit threads
                $ffmpegConvertCommand = sprintf(
                    'timeout 300 ffmpeg -i %s -threads 2 -c:v libx264 -preset fast -c:a aac -b:a 128k -movflags +faststart %s',
                    escapeshellarg($fullTempPath),
                    escapeshellarg($convertedPath)
                );

                Log::info("Running conversion command: " . $ffmpegConvertCommand);
                $output = shell_exec($ffmpegConvertCommand . ' 2>&1');

                if (!file_exists($convertedPath) || filesize($convertedPath) < 1024) {
                    Log::error("FFmpeg conversion failed for: " . $this->tempFilePath);
                    Log::error("FFmpeg output: " . $output);
                    throw new \RuntimeException('Video conversion failed or produced an invalid file');
                }

                $finalPath = $convertedPath;
                // Only delete the original MKV after successful conversion and only if we're not going to need it for retry
                if ($this->attempts() >= $this->tries) {
                    Storage::delete($this->tempFilePath);
                }
                Log::info("Conversion completed successfully");
            }

            // Upload final video (original or converted)
            Log::info("Uploading video to media library");
            $videoMedia = MediaUploader::fromSource($finalPath)
                ->useHashForFilename('sha1')
                ->toDestination('public', 'videos')
                ->upload();

            // Generate thumbnail from the final video
            $thumbnailPath = storage_path('app/tmp/'.uniqid('thumb_').'.jpg');

            // Add timeout to thumbnail generation too
            $ffmpegThumbnailCommand = sprintf(
                'timeout 60 ffmpeg -i %s -ss 00:00:01.000 -vframes 1 -vf "scale=500:500:force_original_aspect_ratio=increase,crop=500:500" -q:v 5 %s',
                escapeshellarg($finalPath),
                escapeshellarg($thumbnailPath)
            );

            Log::info("Generating thumbnail");
            $thumbOutput = shell_exec($ffmpegThumbnailCommand . ' 2>&1');

            if (file_exists($thumbnailPath) && filesize($thumbnailPath) > 0) {
                $thumbnailMedia = MediaUploader::fromSource($thumbnailPath)
                    ->useFilename(pathinfo($finalPath, PATHINFO_FILENAME).'_thumb')
                    ->useHashForFilename('sha1')
                    ->toDestination('public', 'videos/thumbs')
                    ->upload();

                $videoMedia->setAttribute('video_thumbnail_id', $thumbnailMedia->id);
                $videoMedia->save();
                unlink($thumbnailPath);
                Log::info("Thumbnail created successfully");
            } else {
                Log::warning("Failed to create thumbnail, continuing without it. Output: " . $thumbOutput);
            }

            // Clean up
            if ($isMkv && file_exists($finalPath)) {
                unlink($finalPath); // Clean up converted MP4
            }

            $this->model->attachMedia($videoMedia, ['default']);

            $videoMedia->media_groups()->sync([
                MediaGroup::first()->id
            ]);

            Log::info("Media attached to model successfully");

            // Delete the temporary file if it still exists and we're not going to need it for retry
            if (Storage::exists($this->tempFilePath)) {
                Storage::delete($this->tempFilePath);
            }

            Log::info("Video processing completed successfully");

        } catch (\Exception $e) {
            Log::error("Error processing video: " . $e->getMessage());

            // Only clean up temporary files if we've exhausted all retry attempts
            if ($this->attempts() >= $this->tries) {
                $this->cleanupTemporaryFiles();
                Log::info("Cleaning up files after final attempt");
            } else {
                Log::info("Not cleaning up files as job will be retried. Current attempt: " . $this->attempts());
                // Only clean up intermediate files, not the original
                $this->cleanupIntermediateFiles();
            }

            throw $e; // Rethrow to mark job as failed
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Video processing job failed permanently: " . $exception->getMessage());

        // This is only called after all retries are exhausted
        $this->cleanupTemporaryFiles();
    }

    private function cleanupTemporaryFiles(): void
    {
        // Clean up the original temp file
        if (isset($this->tempFilePath) && Storage::exists($this->tempFilePath)) {
            Storage::delete($this->tempFilePath);
            Log::info("Cleaned up original temp file: " . $this->tempFilePath);
        }

        // Clean up intermediate files too
        $this->cleanupIntermediateFiles();
    }

    private function cleanupIntermediateFiles(): void
    {
        // Try to identify and clean up any intermediate temporary files this job might have created
        // but preserve the original file for potential retries
        $baseName = pathinfo($this->tempFilePath, PATHINFO_FILENAME);
        $potentialTempFiles = [
            storage_path('app/tmp/converted_*.mp4'),
            storage_path('app/tmp/thumb_*.jpg')
        ];

        foreach ($potentialTempFiles as $pattern) {
            foreach (glob($pattern) as $file) {
                if (file_exists($file)) {
                    unlink($file);
                    Log::info("Cleaned up intermediate temp file: " . $file);
                }
            }
        }
    }
}
