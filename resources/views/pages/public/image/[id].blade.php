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
       public $likesCount = 0;
       public $dislikesCount = 0;
       public $currentVote = null;

       public function mount($id): void
       {
          $this->id = $id;
          $this->media = Media::find($id);
            $this->likesCount = $this->media->likes_count;
            $this->dislikesCount = $this->media->dislikes_count;
            $this->currentVote = $this->media->current_vote;

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


    <div class="max-w-6xl px-8 pt-12 pb-20 mx-auto">
    <div class="flex justify-center card">

    <div class=" bg-primary-700 space-x-4  border-t border-gray-200 dark:border-gray-700 dark:bg-gray-900  mb-1 text-base bg-white rounded-lg dark:bg-gray-900"></div>

    <img src="{{ $media->getUrl() }}" alt="Image">



    </div>

        <div class="flex justify-end my-8">

            <livewire:vote
                :currentVote="$currentVote" :likesCount="$likesCount" :dislikesCount="$dislikesCount"
                :id="$id"/>

        </div>


        <livewire:comments :model="$media" />


</div>

    @endvolt
</x-layouts.marketing>
