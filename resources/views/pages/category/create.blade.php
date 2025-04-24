<?php

use Illuminate\Support\Facades\Http;

use function Laravel\Folio\{middleware, name};
use function Livewire\Volt\{state, with};
use Livewire\Volt\Component;
use App\Models\Category;
use Livewire\WithPagination;
use App\Actions\UploadImage;
use Livewire\WithFileUploads;

name('category.create');
middleware(['auth', 'verified', 'can:access-admin-panel']);

new class extends Component {

        use WithPagination;
    use WithFileUploads;
    public $name;
    public $image;
    public $slug;


       public function submit(): void
        {

        $this->validate([
            'name' => 'required|string|min:3|max:100|unique:categories,name',
            'slug' => 'nullable|min:1|max:100|unique:categories,slug',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:10048',
        ]);

        $category = Category::create([
            'name' => $this->name,
            'slug' => $this->slug,
        ]);


try {

        $uploadImageAction = app()->make(UploadImage::class)->handle($category, $this->image);
} catch (\Exception $e) {

}
        $this->name = "";
        $this->slug = "";

        session()->flash('message', 'Category created successfully.');
    }

};



?>

<style>
    .dark img[alt="Kewlor Logo"] {
        filter: invert(1);
    }
</style>

<x-layouts.app>

    <x-slot name="header">
        <h2 class="text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Create Category') }}
        </h2>
    </x-slot>


    @volt('category.create')



    <div>
        <form wire:submit.prevent="submit" class="w-full" enctype="multipart/form-data">
   @if (session()->has('message'))
    <div class="p-4 mb-4 text-green-700 bg-green-100 rounded">
            {{ session('message') }}
        </div>
   @endif

        <x-ui.input wire:model="name" label="name" id="name" name="name" />
        <x-ui.input wire:model="slug" label="Slug" id="slug" name="slug" />
        <x-ui.input wire:model="image" label="image" id="image" name="image" type="file" />
            <div class="my-4 ">
        <button type="submit" class="mt-2 px-4 py-2 bg-primary-600 text-white rounded" >
                    Create
        </button>
</div>

    </form>
    </div>

    @endvolt

</x-layouts.app>
