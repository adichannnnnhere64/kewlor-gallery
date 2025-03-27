<?php

use function Laravel\Folio\{middleware, name};
use Livewire\Volt\Component;
use App\Models\LiveEventGallery;

name('home');

new class extends Component
{
    public $liveEvents;

    public function mount(): void
    {
        $this->liveEvents = LiveEventGallery::query()->orderBy('date')->get();
    }

};

?>

<x-layouts.marketing>


    <x-ui.marketing.breadcrumbs :crumbs="[]" />
    <div>
        <h1 class="max-w-6xl mx-auto px-8 mt-8 font-bold text-primary-700 text-2xl">Gallery</h1>
    </div>
    @volt('home')



    <div class="max-w-6xl mx-auto ">
    @include('live-event-gallery.public-gallery')
</div>

    @endvolt


</x-layouts.marketing>



