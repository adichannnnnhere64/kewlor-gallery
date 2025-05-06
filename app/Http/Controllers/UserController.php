<?php

namespace App\Http\Controllers;

use App\Actions\CreateLiveEvent;
use App\Http\Requests\LiveEventGalleryRequest;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {

        $data = User::query()->orderBy('date')->paginate(10);

        if ($request->search) {
            $data = User::query()->where('name', 'like', '%'.$request->search.'%')->orderBy('date')->paginate(10);
        }

        if (request()->ajax()) {
            return view('tables.user', ['data' => $data]);
        }

        return view('pages.user.index', ['data' => $data]);
    }
    public function delete(User $model): mixed
    {
        $model->delete();

        return response()->json(['message' => 'success']);

    }
}
