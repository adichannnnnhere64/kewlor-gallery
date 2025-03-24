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

    @volt('home')
    <div class="relative flex flex-col items-center justify-center w-full h-auto overflow-hidden" x-cloak>

    @include('live-event-gallery.public-gallery')

    </div>
    @endvolt

</x-layouts.marketing>
