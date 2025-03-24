@if (isset($liveEvents) && $liveEvents->isNotEmpty())

  <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 50px;
        }
        .thumbnail-container {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .thumbnail {
            cursor: pointer;
            border-radius: 5px;
            width: 150px;
            height: auto;
        }

        /* Dialog styles */
        dialog {
            border: none;
            padding: 0;
            background: transparent;
        }

        /* Blurred & darkened background */
        dialog::backdrop {
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(8px);
        }

        /* Lightbox Image */
        .lightbox img {
            max-width: 90vw;
            max-height: 90vh;
            border-radius: 5px;
        }

        /* Close Button */
        .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 20px;
            color: white;
            background: none;
            border: none;
            cursor: pointer;
        }
    </style>

    <div class="grid w-full grid-cols-3 gap-8 mt-8 max-w-6xl">
    @foreach ($liveEvents as $liveEvent)
      <div>
          <x-ui.card-image  :key="$liveEvent->id" :title="$liveEvent->date" :image="$liveEvent->getMedia('default')->first()?->getUrl()" :detailsUrl="route('live-event.show', ['id' => $liveEvent->id])" />
</div>
    @endforeach


</div>

@endif
