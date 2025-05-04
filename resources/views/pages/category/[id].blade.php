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
    public $description;
    public $slug;
    public $existingImage = null;
    public $imageUploaded = false;

    public function mount($id): void
    {
        $this->id = $id;
        $category = Category::find($this->id);
        if ($category) {
            $this->name = $category->name;
            $this->slug = $category->slug;
            $this->description = $category->description;

            // Get existing image
            if ($category->hasMedia('default')) {
                $this->existingImage = $category->getMedia('default')->first()->getUrl();
            }
        }
    }

    public function updatedImage()
    {
        $this->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10048',
        ]);

        // Set flag that image is ready for processing
        if ($this->image) {
            $this->imageUploaded = true;
        }
    }

    // Generate slug from name if slug is empty
    public function updatedName()
    {
        if (empty($this->slug)) {
            $this->slug = \Illuminate\Support\Str::slug($this->name);
        }
    }

    public function with(): array
    {
        return [
            'category' => Category::query()->find($this->id),
        ];
    }

    public function submit(): void
    {
        // Validate the input
        $data = $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable',
            'slug' => 'nullable|min:1|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10048',
        ]);

        $category = Category::find($this->id);

        if (!$category) {
            session()->flash('error', 'Category not found!');
            return;
        }

        // Only process image if new one is uploaded
        if (isset($data['image']) && $this->image) {
            try {
                // Remove existing media
                $category->media()->delete();

                // Upload new image
                app()->make(UploadImage::class)->handle($category, $this->image);

                // Update existingImage for display
                if ($category->hasMedia('default')) {
                    $this->existingImage = $category->getMedia('default')->first()->getUrl();
                }
            } catch (\Exception $e) {
                session()->flash('error', 'Error uploading image: ' . $e->getMessage());
                return;
            }
        }

        // Update category details
        $category->update([
            'name' => $this->name,
            'description' => $this->description,
            'slug' => $this->slug ?: \Illuminate\Support\Str::slug($this->name),
        ]);

        // Reset the form image input
        $this->image = null;
        $this->imageUploaded = false;

        session()->flash('message', 'Category updated successfully!');
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
        <div class="flex justify-between items-center">
        <h2 class="text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Edit Category') }}
        </h2>
            <a href="/ideas/{{ $id }}" class="bg-orange-700 hover:bg-orange-800 text-white font-bold py-2 px-4 rounded">View</a>
</div>
    </x-slot>
    @volt('category.edit')
    <div>
        <form wire:submit.prevent="submit" class="space-y-4">
            @if (session()->has('message'))
                <div class="p-4 mb-4 text-green-700 bg-green-100 rounded">
                    {{ session('message') }}
                </div>
            @endif

            @if (session()->has('error'))
                <div class="p-4 mb-4 text-red-700 bg-red-100 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <x-ui.input required wire:model="name" label="Name" id="name" name="name" />

            <x-ui.input wire:model="slug" label="Slug" id="slug" name="slug" />
            <x-ui.textarea wire:model="description" label="Description" id="description" description="description" />

            <div>
                <x-ui.app.fileupload wire:model="image" :image />

                <div class="mt-3 flex items-center space-x-4">
                    @if ($image)
                        <div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">New Image:</p>
                            <img src="{{ $image->temporaryUrl() }}" class="w-32 h-32 object-cover rounded border border-gray-300 dark:border-gray-700" alt="New image preview">
                        </div>
                    @endif

                    @if ($existingImage)
                        <div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Current Image:</p>
                            <img src="{{ $existingImage }}" class="w-32 h-32 object-cover rounded border border-gray-300 dark:border-gray-700" alt="Current category image">
                        </div>
                    @endif
                </div>
            </div>

            <div class="my-4">
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded">
                    Update
                </button>
            </div>
        </form>
    </div>
    @endvolt
</x-layouts.app>
