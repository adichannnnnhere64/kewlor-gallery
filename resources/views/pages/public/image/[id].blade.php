<?php

use Illuminate\Support\Facades\Http;

use function Laravel\Folio\{middleware, name};
use function Livewire\Volt\{state, with};
use Livewire\Volt\Component;
use App\Models\LiveEventGallery;
use Livewire\WithPagination;
use App\Models\Media;

name('public.image.show');

new class extends Component {

       public $media;

        public function mount($id): void
        {
            $this->media = Media::find($id);

        }
};


#with(fn () => ['posts' => 'adicchans']);

?>

<style>
    .dark img[alt="Genesis Logo"] {
        filter: invert(1);
    }
</style>

<x-layouts.marketing>

    <x-ui.marketing.breadcrumbs :crumbs="[['text' => 'Image']]" />
    @volt('public.image.show')


    <div class="max-w-6xl px-8 pt-12 pb-20 mx-auto">
    <div class="flex justify-center card">

    <div class="lg:ml-12 bg-primary-700 space-x-4 p-1 mb-1 ml-1 lg:ml-12 border-t border-gray-200 dark:border-gray-700 dark:bg-gray-900 p-6 mb-1 text-base bg-white rounded-lg dark:bg-gray-900"></div>

    <img src="{{ $media->getUrl() }}" alt="Image">


    </div>


        <livewire:comments :model="$media" />


</div>

    @endvolt
</x-layouts.marketing>
