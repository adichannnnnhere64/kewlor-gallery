<?php

namespace App\Http\Controllers;

use App\Actions\CreateLiveEvent;
use App\Http\Requests\LiveEventGalleryRequest;
use App\Models\LiveEventGallery;
use Illuminate\Http\Request;

class LiveEventGalleryController extends Controller
{

    public function index()
    {
        return view('pages.live-event.index', [
            'data' => LiveEventGallery::query()->orderBy('date')->paginate(10)
        ]);
    }


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
