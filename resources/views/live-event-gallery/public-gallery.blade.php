@if (isset($liveEvents) && $liveEvents->isNotEmpty())
    <div class="grid w-full lg:grid-cols-6 sm:grid-cols-2 gap-2 mt-8 max-w-6xl px-8">
    @foreach ($liveEvents as $liveEvent)
          <x-ui.card-image  :key="$liveEvent->id"  :title="$liveEvent->name" :description="$liveEvent->date" :image="$liveEvent->getMedia('default')->first()?->getUrl()" :detailsUrl="route('live-event.show', ['id' => $liveEvent->id])" />
    @endforeach


</div>

@endif
