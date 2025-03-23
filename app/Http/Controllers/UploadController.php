<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Actions\UploadImage;

class UploadController extends Controller
{
    public function store(Request $request, UploadImage $uploadImage)
    {
        $uploadImage->handle();
    }
}
