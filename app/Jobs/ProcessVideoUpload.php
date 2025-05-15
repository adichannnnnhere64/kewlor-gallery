<?php
namespace App\Jobs;
use App\Models\LiveEventGallery;
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
                    Storage::delete($this->tempFilePath);
                    throw new \RuntimeException('Video conversion failed or produced an invalid file');
                }

                $finalPath = $convertedPath;
                Storage::delete($this->tempFilePath); // Remove original MKV
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
            Log::info("Media attached to model successfully");

            // Delete the temporary file if it still exists
            if (Storage::exists($this->tempFilePath)) {
                Storage::delete($this->tempFilePath);
            }

            Log::info("Video processing completed successfully");

        } catch (\Exception $e) {
            Log::error("Error processing video: " . $e->getMessage());
            // Clean up any temporary files that might be left
            $this->cleanupTemporaryFiles();
            throw $e; // Rethrow to mark job as failed
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Video processing job failed: " . $exception->getMessage());
        $this->cleanupTemporaryFiles();
    }

    private function cleanupTemporaryFiles(): void
    {
        // Clean up the original temp file
        if (isset($this->tempFilePath) && Storage::exists($this->tempFilePath)) {
            Storage::delete($this->tempFilePath);
        }

        // Try to identify and clean up any other temporary files this job might have created
        $baseName = pathinfo($this->tempFilePath, PATHINFO_FILENAME);
        $potentialTempFiles = [
            storage_path('app/tmp/converted_*' . $baseName . '*.mp4'),
            storage_path('app/tmp/thumb_*' . $baseName . '*.jpg')
        ];

        foreach ($potentialTempFiles as $pattern) {
            foreach (glob($pattern) as $file) {
                if (file_exists($file)) {
                    unlink($file);
                    Log::info("Cleaned up temp file: " . $file);
                }
            }
        }
    }
}
