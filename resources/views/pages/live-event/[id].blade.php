<?php

use Illuminate\Support\Facades\Http;

use function Laravel\Folio\{middleware, name};
use function Livewire\Volt\{state, with};
use Livewire\Volt\Component;
use App\Models\LiveEventGallery;
use Livewire\WithPagination;

name('live-event.edit');
middleware(['auth', 'verified', 'can:access-admin-panel']);

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
        ];
    }

       public function submit(): void
        {
        // Validate the input
        $this->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
        ]);

        $liveEvent = LiveEventGallery::find($this->id);
        if ($liveEvent) {
            $liveEvent->update([
                'name' => $this->name,
                'date' => $this->date,
            ]);

            session()->flash('message', 'Event updated successfully!');
            #$this->dispatch('eventUpdated');
        }
    }

};


#with(fn () => ['posts' => 'adicchans']);

?>

<style>
    .dark img[alt="Genesis Logo"] {
        filter: invert(1);
    }
</style>

<x-layouts.app>

    <x-slot name="header">
        <h2 class="text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Edit Live Event') }}
        </h2>
    </x-slot>


    @volt('live-event.edit')

    <div>
        <form wire:submit.prevent="submit">

            @if (session()->has('message'))
    <div class="p-4 mb-4 text-green-700 bg-green-100 rounded">
        {{ session('message') }}
    </div>
@endif

        <x-ui.input wire:model="name" label="name" id="name" name="name" />
        <x-ui.input wire:model="date" label="Date" id="date" name="date" type="date" />
            <div class="my-4 ">
        <button  type="submit" class="mt-2 px-4 py-2 bg-blue-600 text-white rounded" >
                Update
        </button>
</div>

            <h1>Add images</h1>

               <div wire:ignore>
            <x-ui.app.uppy :endpoint="route('upload', $id)">
            </x-ui.app.uppy>
                    </div>

            <div>
                @include('live-event-gallery.images')
            </div>
    </form>
    </div>

    @endvolt

</x-layouts.app>
