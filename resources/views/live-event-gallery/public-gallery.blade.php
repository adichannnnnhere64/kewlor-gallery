@if (isset($liveEvents) && $liveEvents->isNotEmpty())

<div class="flex-wrap gap-4 justify-evenly  flex items-center w-full max-w-6xl px-8 pt-12 pb-20 mx-auto">
    @foreach ($liveEvents as $liveEvent)
        <x-ui.live-event-card :key="$liveEvent->id"  :data="$liveEvent"/>
    @endforeach
</div>

@endif
