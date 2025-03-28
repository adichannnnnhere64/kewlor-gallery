<?php

namespace App\Livewire;

use App\Actions\VoteToggle;
use App\Models\Media;
use Cog\Laravel\Love\ReactionType\Models\ReactionType;
use Livewire\Component;

class Tubol extends Component
{
    public $id = null;

    public $isLiked = false;

    public $sortBy = '';

    public $likeCount = 0;

    public $isDisliked = false;

    public $dislikeCount = 0;

    public function mount(): void
    {
        $this->fetchVotes();
    }

    public function dislike(VoteToggle $action)
    {
        $user = auth()->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $model = Media::find($this->id);

        if (! $model) {
            return;
        }

        $action->handle($model, 'dislike');

        $this->fetchVotes();
        $this->dispatch('$refresh');
    }

    private function fetchVotes()
    {

        if ($this->id) {
            $user = auth()->user();
            $model = Media::find($this->id);

            if ($model) {
                $reactantFacade = $model->viaLoveReactant();
                $likeTypeId = ReactionType::where('name', 'Like')->first()->id;

                $this->isLiked = $reactantFacade->isReactedBy($user, 'Like');
                $this->likeCount = $reactantFacade->getReactions()->where('reaction_type_id', $likeTypeId)->count();
                $dislikeTypeId = ReactionType::where('name', 'Dislike')->first()->id;

                $this->isDisliked = $reactantFacade->isReactedBy($user, 'Dislike');
                $this->dislikeCount = $reactantFacade->getReactions()->where('reaction_type_id', $dislikeTypeId)->count();

            }
        }

    }

    public function like(VoteToggle $action)
    {

        $user = auth()->user();
        if (! $user) {
            return redirect()->route('login');
        }

        $model = Media::find($this->id);

        if (! $model) {
            return;
        }

        $action->handle($model, 'like');

        $this->fetchVotes();
        $this->dispatch('$refresh');
    }

    public function render()
    {
        return <<<'HTML'
            <div wire:key="vax-{{ $id }}-{{ $sortBy }}" class="flex">
                    @if ($id)
                        <div class="flex space-x-1" x-data="{ isLiked: @entangle('isLiked'), likeCount: @entangle('likeCount') }">
                            <div x-text="likeCount"></div>
                            <button wire:click="like" x-on:click="isLiked = !isLiked">
                                <span x-show="!isLiked">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 lg:h-4 lg:w-4"  viewBox="0 0 24 24" fill="none" stroke="#656172" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-thumb-up">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M7 11v8a1 1 0 0 1 -1 1h-2a1 1 0 0 1 -1 -1v-7a1 1 0 0 1 1 -1h3a4 4 0 0 0 4 -4v-1a2 2 0 0 1 4 0v5h3a2 2 0 0 1 2 2l-1 5a2 3 0 0 1 -2 2h-7a3 3 0 0 1 -3 -3" />
                                    </svg>
                                </span>
                                <span x-show="isLiked">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 lg:h-4 lg:w-4" viewBox="0 0 24 24" fill="#656172" class="icon icon-tabler icons-tabler-filled icon-tabler-thumb-up">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M13 3a3 3 0 0 1 2.995 2.824l.005 .176v4h2a3 3 0 0 1 2.98 2.65l.015 .174l.005 .176l-.02 .196l-1.006 5.032c-.381 1.626 -1.502 2.796 -2.81 2.78l-.164 -.008h-8a1 1 0 0 1 -.993 -.883l-.007 -.117l.001 -9.536a1 1 0 0 1 .5 -.865a2.998 2.998 0 0 0 1.492 -2.397l.007 -.202v-1a3 3 0 0 1 3 -3z" />
                                        <path d="M5 10a1 1 0 0 1 .993 .883l.007 .117v9a1 1 0 0 1 -.883 .993l-.117 .007h-1a2 2 0 0 1 -1.995 -1.85l-.005 -.15v-7a2 2 0 0 1 1.85 -1.995l.15 -.005h1z" />
                                    </svg>
                                </span>
                            </button>
                        </div>

                <div class="px-1">|</div>


                        <div class="flex space-x-1" x-data="{ isDisliked: @entangle('isDisliked'), dislikeCount: @entangle('dislikeCount') }">
                            <div x-text="dislikeCount"></div>
                            <button wire:click="dislike" x-on:click="isDisliked = !isDisliked">
                                <span x-show="!isDisliked">
                            <svg  xmlns="http://www.w3.org/2000/svg"  class="h-6 w-6 lg:h-4 lg:w-4"  viewBox="0 0 24 24"  fill="none"  stroke="#656172"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-thumb-down"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 13v-8a1 1 0 0 0 -1 -1h-2a1 1 0 0 0 -1 1v7a1 1 0 0 0 1 1h3a4 4 0 0 1 4 4v1a2 2 0 0 0 4 0v-5h3a2 2 0 0 0 2 -2l-1 -5a2 3 0 0 0 -2 -2h-7a3 3 0 0 0 -3 3" /></svg>

                                </span>
                                <span x-show="isDisliked">
                            <svg  xmlns="http://www.w3.org/2000/svg"  class="h-6 w-6 lg:h-4 lg:w-4"  viewBox="0 0 24 24"  fill="#656172"  class="icon icon-tabler icons-tabler-filled icon-tabler-thumb-down"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M13 21.008a3 3 0 0 0 2.995 -2.823l.005 -.177v-4h2a3 3 0 0 0 2.98 -2.65l.015 -.173l.005 -.177l-.02 -.196l-1.006 -5.032c-.381 -1.625 -1.502 -2.796 -2.81 -2.78l-.164 .008h-8a1 1 0 0 0 -.993 .884l-.007 .116l.001 9.536a1 1 0 0 0 .5 .866a2.998 2.998 0 0 1 1.492 2.396l.007 .202v1a3 3 0 0 0 3 3z" /><path d="M5 14.008a1 1 0 0 0 .993 -.883l.007 -.117v-9a1 1 0 0 0 -.883 -.993l-.117 -.007h-1a2 2 0 0 0 -1.995 1.852l-.005 .15v7a2 2 0 0 0 1.85 1.994l.15 .005h1z" /></svg>

                                </span>
                            </button>
                        </div>

                    @endif
            </div>
        HTML;
    }
}
