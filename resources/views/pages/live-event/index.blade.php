<?php

use Illuminate\Support\Facades\Http;

use function Laravel\Folio\{middleware, name};
use function Livewire\Volt\{state};
use Livewire\Volt\Component;

name('live-event');
middleware(['auth', 'verified']);

state(['data' => []]);

new class extends Component {
//    public $data = [];

    public function mount()
    {
 //       $this->data = $data;
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
            {{ __('Live Events') }}
        </h2>
    </x-slot>

    @volt('live-event')
    <div>
        <livewire:live-event-gallery-table />

    </div>

    @endvolt
</x-layouts.app>
