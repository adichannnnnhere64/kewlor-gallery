<?php

use Illuminate\Support\Facades\Http;

use function Laravel\Folio\{middleware, name};
use Livewire\Volt\Component;

name('upload');
middleware(['auth', 'verified']);

new class extends Component {
    public $readme = '';

    public function mount()
    {
        $this->readme = Http::get('https://raw.githubusercontent.com/thedevdojo/genesis/main/README.md')->body();
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
            {{ __('Upload') }}
        </h2>
    </x-slot>

    @volt('upload')
    <div>
        <x-ui.app.uppy>


        </x-ui.app.uppy>
    </div>

    @endvolt
</x-layouts.app>
