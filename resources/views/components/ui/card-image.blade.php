<?php


use Livewire\Volt\Component;
use App\Models\Media;
use App\Actions\VoteToggle;
use function Livewire\Volt\{state};

new class extends Component {

    public $count = 0;
    public $showComment = false;
    public $image = '';
    public $id = null;
    public $title = '';
    public $sortBy = '';
    public $categories = [];
    public $model = null;
    public $description = '';
    public $detailsUrl = '';
    public $liveEventId = 0;
    public $currentVote = false;
    public $voteCount = 0;
    public $likesCount = 0;
    public $dislikesCount = 0;
    public $commentsCount = 0;


}

?>


<div >
    <div>
        <div class="p-2 transition-transform duration-300  cursor-pointer group dark:bg-gray-900 rounded-xl hover:-translate-y-1 hover:shadow-xl border-slate-100 dark:border-white/10">
            <a href="{{ $detailsUrl }}?event={{ $liveEventId }}">
            <img src="{{ $image }}" class="object-cover w-full bg-white lg:h-[200px] border p-2 rounded-md" />
            </a>

            @if ($showComment)
            <div class="mt-2 flex items-center justify-end space-x-2 text-lg lg:text-sm  lg:items-end px-2 text-gray-500">

                <div class="flex items-center">
@if ($model->aggregate_type === 'image')
<svg  xmlns="http://www.w3.org/2000/svg"  class="h-5 w-5"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-photo"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 8h.01" /><path d="M3 6a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v12a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3v-12z" /><path d="M3 16l5 -5c.928 -.893 2.072 -.893 3 0l5 5" /><path d="M14 14l1 -1c.928 -.893 2.072 -.893 3 0l3 3" /></svg>
@else
<svg  xmlns="http://www.w3.org/2000/svg"  class="h-5 w-5"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-video"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 10l4.553 -2.276a1 1 0 0 1 1.447 .894v6.764a1 1 0 0 1 -1.447 .894l-4.553 -2.276v-4z" /><path d="M3 6m0 2a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-8a2 2 0 0 1 -2 -2z" /></svg>
@endif


</div>
                <div class="flex items-center space-x-1">
                <span>{{ $commentsCount }}</span> <svg  xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 lg:h-4 lg:w-4"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-message"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 9h8" /><path d="M8 13h6" /><path d="M18 4a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-5l-5 3v-3h-2a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h12z" /></svg>
                </div>

<livewire:vote
:model="$model"
@refresh="refresh"  :id="$id" :currentVote="$currentVote" :likesCount="$likesCount" :dislikesCount="$dislikesCount" :sortBy="$sortBy" :key="$id" />

            </div>
            @endif

            <div class="flex justify-end mt-1 text-sm text-gray-500">
            </div>
        </div>
    </div>
</div>
