<?php

use function Livewire\Volt\{state};
use App\Models\LiveEventGallery;
use App\Models\Category;
use App\Models\Media;

state([
    'thumbnails' => [],
    'liveEventId' => null
]);

$deleteImage = function ($id) {
    $image = Media::find($id);
    $image->delete();

    $liveEvent = LiveEventGallery::find($this->liveEventId);
    $this->thumbnails = $liveEvent->getMedia('default');

//    session()->flash('message', 'Image deleted successfully!');

};

?>

<div>
@volt('gallery-thumbnail')
    <div>


@if (isset($thumbnails) && $thumbnails->isNotEmpty())

<div class="flex my-4 flex-row flex-wrap gap-4" >
    @foreach ($thumbnails as $image)


            <div
wire:key="delete-{{ $image->id }}"
                class="flex justify-center flex-col items-center" >

        <img
            src="{{ $image->findVariant('thumbnail')?->getUrl() ?? $image?->video_thumbnail }}"
            alt="{{ $image->name }}"
            class="w-34 h-34 rounded-lg object-cover"
        />
                    <button

                wire:confirm="Are you sure you want to delete this image?"
            wire:click="deleteImage({{ $image->id }})" class="cursor-pointer text-red-600 bg-red-200 p-1 rounded-lg mt-2 ">delete</button>
                        </div>

    @endforeach

</div>

@endif
</div>
@endvolt
</div>
