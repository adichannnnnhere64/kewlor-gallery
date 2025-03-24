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

        public function mount($id)
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
        ];
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

    @volt('live-event.show')
    <div class="relative flex flex-col items-center justify-center w-full h-auto overflow-hidden" x-cloak>

        <div class="flex flex-wrap justify-start gap-4 w-full max-w-6xl px-8 pt-12 pb-20 mx-auto">
        @foreach ($images as $image)

            <x-ui.card-image :image="$image->getUrl()" />
        @endforeach

        </div>

    </div>
    @endvolt

</x-layouts.marketing>
