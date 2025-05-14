<?php

namespace App\Actions;

use App\Models\LiveEventGallery;
use Illuminate\Http\UploadedFile;
use App\Jobs\ProcessVideoUpload;
use Illuminate\Support\Facades\Storage;

final class UploadVideo
{
    public function handle(LiveEventGallery $model, UploadedFile $file): void
    {
        // Store the file temporarily with a unique name
        $tempPath = 'tmp/videos/' . uniqid('video_') . '.' . $file->getClientOriginalExtension();
        Storage::put($tempPath, file_get_contents($file->getRealPath()));

        // Dispatch the job with the temporary path
        ProcessVideoUpload::dispatch($model, $tempPath)
            ->onQueue('default');
    }
}
