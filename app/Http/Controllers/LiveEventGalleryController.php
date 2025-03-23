<?php

namespace App\Http\Controllers;

use App\Actions\CreateLiveEvent;
use App\Http\Requests\LiveEventGalleryRequest;
use Illuminate\Http\Request;

class LiveEventGalleryController extends Controller
{
    public function store(LiveEventGalleryRequest $request, CreateLiveEvent $action)
    {
        $model = $action->handle($request->array(['name', 'date']));

        if (!$model) {
            return response([
                'message' => 'Something went wrong',
            ]);
        }

        return response([
            'message' => 'success',
        ]);

    }
}
