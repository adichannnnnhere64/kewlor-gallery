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
       public $id;
       public $liveEventId;
       public $likesCount = 0;
       public $dislikesCount = 0;
       public $currentVote = null;

       public function mount($id): void
       {
          $this->id = $id;
          $this->liveEventId = request('event');

          $this->media = Media::find($id);
            $this->likesCount = $this->media?->likes_count ?? 0;
            $this->dislikesCount = $this->media?->dislikes_count ?? 0;
            $this->currentVote = $this->media?->current_vote;

       }
};


?>

<style>
    .dark img[alt="Kewlor Logo"] {
        filter: invert(1);
    }
</style>

<x-layouts.marketing>

    <x-ui.marketing.breadcrumbs :crumbs="[['text' => 'Image']]" />
    @volt('public.image.show')


    <div class="max-w-6xl px-8 pt-4 pb-20 mx-auto">
        <div class="w-20 mb-4">
    <x-ui.button tag="a" type="secondary"  href="{{ route('live-event.show', ['id' => $this?->liveEventId ] ) }}" class="mb-8 inline">
    <div class="flex items-center space-x-1 justify-center">
    <svg  xmlns="http://www.w3.org/2000/svg" class="h-4"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-arrow-left"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M5 12l6 6" /><path d="M5 12l6 -6" /></svg>

    <span>Back</span>

    </div>


    </x-ui.button>
</div>
    <div class="flex justify-center card">

    <div class=" bg-primary-700 space-x-4  border-t border-gray-200 dark:border-gray-700 dark:bg-gray-900  mb-1 text-base bg-white rounded-lg dark:bg-gray-900"></div>


    @if(Str::startsWith($media->mime_type, 'image/'))
    <img src="{{ $media->getUrl() }}" alt="Image">
@elseif(Str::startsWith($media->mime_type, 'video/'))
    <video controls class="max-w-full">
        <source src="{{ $media->getUrl() }}" type="{{ $media->mime_type }}">
        Your browser does not support the video tag.
    </video>
@else
    <p>Unsupported media type</p>
@endif



    </div>

        <div class="flex justify-end my-8 space-x-4 items-center">
            <div class="w-30 ">
            <x-ui.button tag="a" href="{{ $media->getUrl() }}" target="_blank"> <div class="flex items-center space-x-1"> <span> Download </span> <svg class="w-5"  xmlns="http://www.w3.org/2000/svg"   viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-download"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><path d="M7 11l5 5l5 -5" /><path d="M12 4l0 12" /></svg> </div>  </x-ui.button>
</div>
            <livewire:vote
                :currentVote="$currentVote" :likesCount="$likesCount" :dislikesCount="$dislikesCount"
                :id="$id"/>

        </div>


        <livewire:comments :model="$media" />


</div>

    @endvolt
</x-layouts.marketing>
