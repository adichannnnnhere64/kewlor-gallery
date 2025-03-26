<?php

use Illuminate\Support\Facades\Http;

use function Laravel\Folio\{middleware, name};
use function Livewire\Volt\{state, with};
use Livewire\Volt\Component;
use App\Models\LiveEventGallery;
use Livewire\WithPagination;

name('live-event.create');
middleware(['auth', 'verified', 'can:access-admin-panel']);

new class extends Component {

        use WithPagination;
    public $name;
    public $date;


       public function submit(): void
        {

        $this->validate([
            'name' => 'required|string|min:3|max:100',
            'date' => 'required|date',
            #'slug' => 'nullable|min:1|max:100',
        ]);

        LiveEventGallery::create([
            'name' => $this->name,
            'date' => $this->date,
            #  'slug' => $this->slug
        ]);

        $this->name = "";
        $this->date = "";

        session()->flash('message', 'Event created successfully.');
    }

};



?>

<style>
    .dark img[alt="Genesis Logo"] {
        filter: invert(1);
    }
</style>

<x-layouts.app>

    <x-slot name="header">
        <h2 class="text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Create Live Event') }}
        </h2>
    </x-slot>


    @volt('live-event.create')



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
        <button type="submit" class="mt-2 px-4 py-2 bg-primary-600 text-white rounded" >
                    Create
        </button>
</div>

    </form>
    </div>

    @endvolt

</x-layouts.app>
