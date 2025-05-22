<?php

namespace App\Jobs;

use App\Models\LiveEventGallery;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessBatchUpload implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(Model $model, public string $uploadId) {}

    public function handle()
    {
        $files = Storage::files("tmp/uploads/{$this->uploadId}");

        foreach ($files as $filePath) {
            $mimeType = Storage::mimeType($filePath);

            if (str_starts_with($mimeType, 'image/')) {
                ProcessImageUpload::dispatch($filePath)->onQueue('media');
            } elseif (str_starts_with($mimeType, 'video/')) {
                ProcessVideoUpload::dispatch($filePath)->onQueue('media');
            } elseif (str_starts_with($mimeType, 'audio/')) {
                ProcessAudioUpload::dispatch($filePath)->onQueue('media');
            }
        }
    }
}
