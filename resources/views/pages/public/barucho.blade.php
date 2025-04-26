<?php

use function Livewire\Volt\{with, usesPagination};
    usesPagination();
    with(fn () => ['posts' => App\Models\Media::paginate(2)]);
?>

<x-layouts.app>
    @volt('posts')
     <div>
    @foreach ($posts as $post)
        <div>
            <x-ui.card wire:key="libat-{{$post->id}}"

                :image="$post->image"
                :id="$post->id" />
        </div>
    @endforeach
    {{ $posts->links() }}
    </div>
    @endvolt
</x-layouts.app>
