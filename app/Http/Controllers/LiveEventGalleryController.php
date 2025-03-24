<?php

namespace App\Http\Controllers;

use App\Actions\CreateLiveEvent;
use App\Http\Requests\LiveEventGalleryRequest;
use App\Models\LiveEventGallery;
use Illuminate\Http\Request;

class LiveEventGalleryController extends Controller
{

    public function index(Request $request)
{

    $data = LiveEventGallery::query()->orderBy('date')->paginate(10);

    if ($request->search) {
        $data = LiveEventGallery::query()->where('name', 'like', '%' . $request->search . '%')->orderBy('date')->paginate(10);
    }

    if (request()->ajax()) {
        return view('live-event-gallery._partial', ['data' => $data]);
    }

    return view('pages.live-event.index', ['data' => $data]);
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

    public function edit(LiveEventGallery $model)
    {
        return view('pages.live-event.index', ['data' => $model]);
    }
}
