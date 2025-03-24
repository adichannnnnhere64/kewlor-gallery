<?php

use Illuminate\Support\Facades\Http;

use function Laravel\Folio\{middleware, name};
use function Livewire\Volt\{state, with};
use Livewire\Volt\Component;
use App\Models\LiveEventGallery;
use Livewire\WithPagination;
use Plank\Mediable\Media;

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
    @volt('public.image.show')

    <div class="max-w-6xl px-8 pt-12 pb-20 mx-auto">
        <div class="flex justify-center card">

    <img src="{{ $media->getUrl() }}" alt="Image">
</div>
</div>

    @endvolt
</x-layouts.marketing>
