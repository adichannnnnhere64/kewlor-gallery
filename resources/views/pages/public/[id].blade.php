<?php

use Illuminate\Support\Facades\Http;

use function Laravel\Folio\{middleware, name};
use function Livewire\Volt\{state, with};
use Livewire\Volt\Component;
use App\Models\LiveEventGallery;
use Livewire\WithPagination;

name('live-event.show');

new class extends Component {

        use WithPagination;
       public $id;
    public $name;
    public $date;
    public $images;

        public function mount($id): void
    {
        $this->id = $id;
        $liveEvent = LiveEventGallery::find($this->id);
        $this->images = $liveEvent->getMedia('default');

        if ($liveEvent) {
            $this->name = $liveEvent->name;
            $this->date = $liveEvent->date;
        }
    }

        public function with(): array
    {
        return [
            'live_event' => LiveEventGallery::query()->find($this->id),
            'name' => $this->name
        ];
    }

};


#with(fn () => ['posts' => 'adicchans']);

?>

<style>
    .dark img[alt="Kewlor Logo"] {
        filter: invert(1);
    }
</style>

<x-layouts.marketing>

    @volt('live-event.show')

    <div>

    <x-ui.marketing.breadcrumbs :crumbs="[['text' => $name]]" />
    <div class="flex max-w-6xl mx-auto  justify-start px-8">
        <h1 class="mt-8 font-bold text-primary-700 text-2xl">{{ $name }}</h1>
    </div>

    <div class="relative max-w-6xl mx-auto items-center  w-full h-auto overflow-hidden" x-cloak
         x-data="{ isOpen: false, currentImage: '' }">



        <style>
            /* Thumbnail styles */
            .thumbnail-container {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 1rem;
                width: 100%;
            }

            /* Lightbox styles */
            .lightbox-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.9);
                backdrop-filter: blur(8px);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 1000;
                opacity: 0;
                transition: opacity 0.3s ease;
            }

            .lightbox-overlay {
                opacity: 1;
            }

            .lightbox-content {
                position: relative;
                max-width: 90vw;
                max-height: 90vh;
            }

            .lightbox-image {
                max-width: 100%;
                max-height: 80vh;
                border-radius: 8px;
                box-shadow: 0 0 20px rgba(0, 0, 0, 0.6);
            }

            .close-btn {
                position: absolute;
                top: -40px;
                right: 0;
                font-size: 2rem;
                color: white;
                background: none;
                border: none;
                cursor: pointer;
                transition: transform 0.2s;
            }

            .close-btn:hover {
                transform: scale(1.2);
            }

            /* Make cards clickable */
            .clickable-card {
                cursor: pointer;
                transition: transform 0.2s;
            }

            .clickable-card:hover {
                transform: scale(1.02);
            }

               /* Add this to your existing styles */
    .clickable-card {
        position: relative;
        overflow: hidden;
        border-radius: 0.5rem; /* Match this with your card's border radius */
    }

    .clickable-card img {
        transition: transform 0.3s ease;
        display: block;
        width: 100%;
        height: auto;
    }

    .clickable-card:hover img {
        transform: scale(1.02);
    }

    /* This ensures the overlay is properly positioned */
    .clickable-card > div:first-child {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        pointer-events: none; /* Allows clicking through the overlay */
    }

    /* Make sure the eye button is above the overlay */
    .clickable-card button {
        z-index: 10;
    }
        </style>



        <div class="grid w-full lg:grid-cols-4 sm:grid-cols-2 gap-2 mt-8 max-w-6xl px-8">
            @if ($images->count() > 0)
            @foreach ($images as $image)
                    <x-ui.card-image :key="$image->id" :id="$image->id" :detailsUrl="route('public.image.show', ['id' => $image->id])" :image="$image->getUrl()" />
            @endforeach
                @else

                <p>No images found..</p>

                @endif

        </div>



            <div x-show="isOpen" class="lightbox-overlay" >
                <div class="lightbox-content" @click.outside="isOpen = false">
                    <button class="close-btn" @click="isOpen = false">×</button>
                    <img x-bind:src="currentImage" alt="Full size image" class="lightbox-image">
                </div>
            </div>

    </div>
    @endvolt
</div>
</x-layouts.marketing>
