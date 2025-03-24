@props([
    'data' => null
])

<div class="h-34 w-34 m-2">
    <a href="{{ route('live-event.show', [ 'id' => $data->id ]) }}" class="font-bold">{{ $data->date }}</a>
    <x-ui.card-image :image="$data->getMedia('default')->first()?->getUrl()" />
</div>
