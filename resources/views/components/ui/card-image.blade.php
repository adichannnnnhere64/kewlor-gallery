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
};

?>


<div wire:key="{{ $liveEventId }}">
    <div>
        <div
            class="p-2 transition-transform duration-300  cursor-pointer group dark:bg-gray-900 rounded-xl hover:-translate-y-1 hover:shadow-xl border-slate-100 dark:border-white/10">
            <a href="{{ $detailsUrl }}?event={{ $liveEventId }}" class="relative block">
                <img src="{{ $image }}" class="object-cover w-full bg-white lg:h-[250px] border p-2 rounded-md" />

                @if ($model->aggregate_type === 'video')
                    <svg class="absolute border-4 border-white rounded-full inset-0 z-[0] m-auto w-20 h-20 z-10"
                        xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64" fill="none">
                        <circle cx="32" cy="32" r="32" fill="#00000080" />
                        <polygon points="26,20 26,44 46,32" fill="#FFFFFF" />
                    </svg>
                @endif
            </a>

            @if ($showComment)
                <div
                    class="mt-2 flex items-center justify-end space-x-2 text-lg lg:text-sm  lg:items-end px-2 text-gray-500">

                    <div class="flex items-center">

                    </div>

                    <livewire:vote :model="$model" @refresh="refresh" :id="$id" :currentVote="$currentVote"
                        :likesCount="$likesCount" :dislikesCount="$dislikesCount" :sortBy="$sortBy" :key="$id" />

                </div>
            @endif

            <div class="flex justify-end mt-1 text-sm text-gray-500">
            </div>
        </div>
    </div>
</div>
