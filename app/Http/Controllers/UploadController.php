<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Actions\UploadImage;
use App\Http\Requests\UploadRequest;
use App\Models\LiveEventGallery;

class UploadController extends Controller
{
    public function store(UploadRequest $request, LiveEventGallery $model, UploadImage $action)
    {
        $action->handle($model, $request->file);

        return response([
            'message' => 'success',
        ]);
    }
}
