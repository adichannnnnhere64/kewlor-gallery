<?php

use Illuminate\Support\Facades\Http;

use function Laravel\Folio\{middleware, name};
use function Livewire\Volt\{state, with};
use App\Actions\UploadImage;
use Livewire\Volt\Component;
use App\Models\Category;
use App\Models\Media;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

name('category.edit');
middleware(['auth', 'verified', 'can:access-admin-panel']);

new class extends Component {

        use WithPagination;
    use WithFileUploads;
       public $id;
    public $name;
    public $image;
    public $slug;

        public function mount($id): void
    {
        $this->id = $id;
        $category = Category::find($this->id);

        if ($category) {
            $this->name = $category->name;
            $this->slug = $category->slug;
        }
    }

        public function with(): array
    {
        return [
            'live_event' => Category::query()->find($this->id),
        ];
    }


    public function reload(): void
    {
        $category = LiveEventGallery::find($this->id);
        $this->images = $category->getMedia('default');
}

       public function submit(): void
        {
        // Validate the input
        $data = $this->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|min:1|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10048',
        ]);

        $category = Category::find($this->id);

        if (isset($data['image'])) {
            $category->media()->delete();

try {

        $uploadImageAction = app()->make(UploadImage::class)->handle($category, $this->image);

} catch (\Exception $e) {

}

        }

        if ($category) {
            $category->update([
                'name' => $this->name,
                'slug' => $this->slug
            ]);

            session()->flash('message', 'Category updated successfully!');
            #$this->dispatch('eventUpdated');
        }
    }

};


#with(fn () => ['posts' => 'adicchans']);

?>

<style>
    .dark img[alt="Kewlor Logo"] {
        filter: invert(1);
    }
</style>

<x-layouts.app>

    <x-slot name="header">
        <h2 class="text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Edit Category') }}
        </h2>
    </x-slot>


    @volt('category.edit')

    <div>
        <form wire:submit.prevent="submit">

            @if (session()->has('message'))
    <div class="p-4 mb-4 text-green-700 bg-green-100 rounded">
        {{ session('message') }}
    </div>
@endif

        <x-ui.input required wire:model="name" label="name" id="name" name="name" />
        <x-ui.input wire:model="slug" label="Slug" id="slug" name="slug" type="slug" />
        <x-ui.input wire:model="image" label="image" id="image" name="image" type="file" />
            <div class="my-4 ">
        <button  type="submit" class="mt-2 px-4 py-2 bg-primary-600 text-white rounded" >
                Update
        </button>
</div>

            </div>
    </form>
    </div>

    @endvolt

</x-layouts.app>
