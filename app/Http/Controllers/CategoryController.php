<?php

namespace App\Http\Controllers;

use App\Actions\CreateLiveEvent;
use App\Http\Requests\LiveEventGalleryRequest;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {

        $data = Category::query()->orderBy('date')->paginate(10);

        if ($request->search) {
            $data = Category::query()->where('name', 'like', '%'.$request->search.'%')->orderBy('date')->paginate(10);
        }

        if (request()->ajax()) {
            return view('tables.category', ['data' => $data]);
        }

        return view('pages.category.index', ['data' => $data]);
    }
    public function delete(Category $model): mixed
    {
        $model->delete();

        return response()->json(['message' => 'success']);

    }
}
