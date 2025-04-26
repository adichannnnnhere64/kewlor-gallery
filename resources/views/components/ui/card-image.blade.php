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


<div wire:key="img-{{ $image }}">
    <div>
        <div class="p-2 transition-transform duration-300  cursor-pointer group dark:bg-gray-900 rounded-xl hover:-translate-y-1 hover:shadow-xl border-slate-100 dark:border-white/10">
            <a href="{{ $detailsUrl }}?event={{ $liveEventId }}">
            <img src="{{ $image }}" class="object-cover w-full bg-white lg:h-[200px] border p-4 rounded-md" />
            </a>

            @if ($showComment)
            <div class="mt-2 flex items-center justify-end space-x-2 text-lg lg:text-sm  lg:items-end px-2 text-gray-500">
                <span class="flex items-center space-x-1">
                {{ $commentsCount }} <svg  xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 lg:h-4 lg:w-4"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-message"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 9h8" /><path d="M8 13h6" /><path d="M18 4a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-5l-5 3v-3h-2a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h12z" /></svg>
                </span>

<livewire:vote
:model="$model"
@refresh="refresh"  :id="$id" :currentVote="$currentVote" :likesCount="$likesCount" :dislikesCount="$dislikesCount" :sortBy="$sortBy" wire:key="img-{{ $id }}-{{ $sortBy }}" />

            </div>
            @endif

            <div class="flex justify-end mt-1 text-sm text-gray-500">
            </div>
        </div>
    </div>
</div>
