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

    public $name = '';
    public $image = null;
    public $slug = '';
    public $description = '';
    public $imageUploaded = false;

    // Add real-time validation
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

    // Generate slug from name
    public function updatedName()
    {
        if (empty($this->slug)) {
            $this->slug = \Illuminate\Support\Str::slug($this->name);
        }
    }

    public function submit(): void
    {
        $this->validate([
            'name' => 'required|string|min:3|max:100|unique:categories,name',
            'description' => 'nullable',
            'slug' => 'nullable|min:1|max:100|unique:categories,slug',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10048',
        ]);

        $category = Category::create([
            'name' => $this->name,
            'description' => $this->description,
            'slug' => $this->slug ?: \Illuminate\Support\Str::slug($this->name),
        ]);

        // Only process image if it exists
        if ($this->image) {
            try {
                app()->make(UploadImage::class)->handle($category, $this->image);
            } catch (\Exception $e) {
                session()->flash('error', 'Error uploading image: ' . $e->getMessage());
                return;
            }
        }

        // Reset form
        $this->reset(['name', 'slug', 'image', 'imageUploaded']);

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
        <form wire:submit.prevent="submit" class="w-full space-y-4" enctype="multipart/form-data">
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

            <x-ui.input wire:model="name" label="Name" id="name" name="name" />
            <x-ui.input wire:model="slug" label="Slug" id="slug" name="slug" />
            <x-ui.textarea wire:model="description" label="Description" id="description" description="description" />


            <div>
                <x-ui.input wire:model="image" label="Image" id="image" name="image" type="file" />
                @if ($image)
                    <div class="mt-2">
                        <img src="{{ $image->temporaryUrl() }}" class="w-32 h-32 object-cover rounded" alt="Preview">
                    </div>
                @endif
            </div>

            <div class="my-4">
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded">
                    Create
                </button>
            </div>
        </form>
    </div>
    @endvolt
</x-layouts.app>
