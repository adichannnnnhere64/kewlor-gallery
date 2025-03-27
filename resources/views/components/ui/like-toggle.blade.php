<?php

use Livewire\Volt\Component;
use App\Models\Media;
use App\Actions\VoteToggle;
use function Livewire\Volt\{state};

new class extends Component {
    public $id = null;
    public $isVoted = false;
    public $voteCount = 0;

    public function mount(): void
    {
        if ($this->id) {
            $user = auth()->user();
            $model = Media::find($this->id);

            if ($model) {
                $reactantFacade = $model->viaLoveReactant();
                $this->isVoted = $reactantFacade->isReactedBy($user, 'Like');
                $this->voteCount = $reactantFacade->getReactions()->count();
            }
        }
    }

    public function voteToggle(VoteToggle $action)
    {
        $user = auth()->user();
        $model = Media::find($this->id);

        if (!$model) return;

        $action->handle($model);

        $reactantFacade = $model->viaLoveReactant();
        $this->isVoted = $reactantFacade->isReactedBy($user, 'Like');
        $this->voteCount = $reactantFacade->getReactions()->count();
        $this->dispatch('$refresh');
    }
}
?>

@volt('like-toggle')
<div>
        @if ($id)
            <div class="flex space-x-2" x-data="{ isVoted: @entangle('isVoted'), voteCount: @entangle('voteCount') }">
                <div x-text="voteCount"></div>
                <button wire:click="voteToggle" x-on:click="isVoted = !isVoted">
                    <span x-show="!isVoted">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-thumb-up">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M7 11v8a1 1 0 0 1 -1 1h-2a1 1 0 0 1 -1 -1v-7a1 1 0 0 1 1 -1h3a4 4 0 0 0 4 -4v-1a2 2 0 0 1 4 0v5h3a2 2 0 0 1 2 2l-1 5a2 3 0 0 1 -2 2h-7a3 3 0 0 1 -3 -3" />
                        </svg>
                    </span>
                    <span x-show="isVoted">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#000000" class="icon icon-tabler icons-tabler-filled icon-tabler-thumb-up">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M13 3a3 3 0 0 1 2.995 2.824l.005 .176v4h2a3 3 0 0 1 2.98 2.65l.015 .174l.005 .176l-.02 .196l-1.006 5.032c-.381 1.626 -1.502 2.796 -2.81 2.78l-.164 -.008h-8a1 1 0 0 1 -.993 -.883l-.007 -.117l.001 -9.536a1 1 0 0 1 .5 -.865a2.998 2.998 0 0 0 1.492 -2.397l.007 -.202v-1a3 3 0 0 1 3 -3z" />
                            <path d="M5 10a1 1 0 0 1 .993 .883l.007 .117v9a1 1 0 0 1 -.883 .993l-.117 .007h-1a2 2 0 0 1 -1.995 -1.85l-.005 -.15v-7a2 2 0 0 1 1.85 -1.995l.15 -.005h1z" />
                        </svg>
                    </span>
                </button>
            </div>
        @endif
</div>
@endvolt

