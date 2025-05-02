<?php

use function Livewire\Volt\{state};
use App\Models\LiveEventGallery;
use App\Models\Category;
use App\Models\Media;

use App\Actions\VoteToggle;

state([
    'thumbnails' => [],
    'liveEventId' => null,
    'categoryFilter' => '',
    'keyValue' => null,
    'isLiked' => false,
    'sortBy' => '',
    'likesCount' => 0,
    'dislikesCount' => 0,
    'currentVote' => null,
    'model' => null,
    'id' => null,
]);

$like = function (VoteToggle $action) {
    $model = $this->model;

    if (!$model) {
        return;
    }

    $action->handle($model, 'like');

    $this->likesCount = $model->likes_count;
    $this->dislikesCount = $model->dislikes_count;

    //    dd('');
};

$dislike = function (VoteToggle $action) {
    $model = $this->model;

    if (!$model) {
        return;
    }

    $action->handle($model, 'dislike');

    $this->likesCount = $model->likes_count;
    $this->dislikesCount = $model->dislikes_count;
};

$deleteImage = function ($id) {};

?>


<div>
    @volt('like-dislike')
        <div class="flex">
            @if ($id)
                <div class="flex space-x-1" x-data="{ likesCount: @entangle('likesCount') }">
                    <div x-text="likesCount" class="dark:text-white"></div>
                    <button wire:click="like">
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 lg:h-4 lg:w-4" viewBox="0 0 24 24"
                                fill="none" stroke="#656172" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-thumb-up">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path
                                    d="M7 11v8a1 1 0 0 1 -1 1h-2a1 1 0 0 1 -1 -1v-7a1 1 0 0 1 1 -1h3a4 4 0 0 0 4 -4v-1a2 2 0 0 1 4 0v5h3a2 2 0 0 1 2 2l-1 5a2 3 0 0 1 -2 2h-7a3 3 0 0 1 -3 -3" />
                            </svg>
                        </span>

                    </button>
                </div>

                <div class="px-1">|</div>


                <div class="flex space-x-1" x-data="{ dislikesCount: @entangle('dislikesCount') }">
                    <div x-text="dislikesCount" class="dark:text-white"></div>
                    <button wire:click="dislike">
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 lg:h-4 lg:w-4" viewBox="0 0 24 24"
                                fill="none" stroke="#656172" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"
                                class="icon icon-tabler icons-tabler-outline icon-tabler-thumb-down">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path
                                    d="M7 13v-8a1 1 0 0 0 -1 -1h-2a1 1 0 0 0 -1 1v7a1 1 0 0 0 1 1h3a4 4 0 0 1 4 4v1a2 2 0 0 0 4 0v-5h3a2 2 0 0 0 2 -2l-1 -5a2 3 0 0 0 -2 -2h-7a3 3 0 0 0 -3 3" />
                            </svg>

                        </span>

                    </button>
                </div>
            @endif
        </div>
    @endvolt
</div>
