<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Actions\UploadImage;
use App\Http\Requests\LiveEventGalleryRequest;

class UploadController extends Controller
{
    public function store(LiveEventGalleryRequest $request, UploadImage $action)
    {
        $action->handle($request->file);
    }
}
