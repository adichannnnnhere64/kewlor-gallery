<?php

use Illuminate\Support\Facades\Http;

use function Laravel\Folio\{middleware, name};
use function Livewire\Volt\{state, with};
use Livewire\Volt\Component;
use App\Models\LiveEventGallery;
use Livewire\WithPagination;

name('live-event.settings');
middleware(['auth', 'verified', 'can:access-admin-panel']);

new class extends Component {

        public $xaxis = '';
        public $yaxis = '';
        public $watermark = '';
        public $bgcolor = '';

        public function mount(): void
        {
            $this->xaxis = setting('xaxis');
            $this->yaxis = setting('yaxis');
            $this->watermark = setting('watermark');
            $this->bgcolor = setting('bgcolor') ?? '#fff';
        }

        public function submit(): void
        {
            setting(['xaxis' => $this->xaxis, 'yaxis' => $this->yaxis, 'watermark' => $this->watermark, 'bgcolor' => $this->bgcolor])->save();
        }
};


?>

<x-layouts.app>

    <x-slot name="header">
        <h2 class="text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Settings') }}
        </h2>
    </x-slot>


    @volt('live-event.settings')
    <form class="block space-y-6" wire:submit="submit">
        <x-ui.input wire:model="yaxis" label="Y Axis" id="yaxis" name="yaxis" />
        <x-ui.input wire:model="xaxis" label="X Axis" id="xaxis" name="xaxis" />
        <x-ui.input wire:model="watermark" label="Watermark" id="watermark" name="watermark" />
        <x-ui.input wire:model="bgcolor" label="Background Color" id="bgcolor" name="bgcolor" />

            <div class="w-20">
                <x-ui.button  type="primary" submit="true">{{ __('Update') }}</x-ui.button>
            </div>
    </form>

    @endvolt
</x-layouts.app>
