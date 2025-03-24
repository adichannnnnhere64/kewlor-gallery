
@if (isset($images) && $images->isNotEmpty())


<div class="flex my-4 flex-row flex-wrap gap-4" >
    @foreach ($images as $image)

        <img
            src="{{ $image->getUrl() }}"
            alt="{{ $image->name }}"
            class="w-34 h-34 rounded-lg object-cover"
        />

    @endforeach

</div>

@endif
