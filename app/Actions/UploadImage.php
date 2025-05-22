<?php

namespace App\Actions;

use App\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ProcessImageUpload;

final class UploadImage
{
    public function handle(Model $model, UploadedFile $file): void
    {
        $tempPath = 'tmp/images/' . uniqid('image_') . '.' . $file->getClientOriginalExtension();
        Storage::put($tempPath, file_get_contents($file->getRealPath()));

        ProcessImageUpload::dispatch($model, $tempPath)
            ->onQueue('media');
    }
}
