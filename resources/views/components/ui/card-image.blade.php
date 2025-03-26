<?php

use Livewire\Volt\Component;
use App\Models\Media;
use App\Actions\VoteToggle;
use function Livewire\Volt\{state};

new class extends Component {
    public $count = 0;
    public $image = '';
    public $id = null;
    public $title = '';
    public $description = '';
    public $detailsUrl = '';
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

                $reactantFacade = $model->viaLoveReactant();
                $this->voteCount = $reactantFacade->getReactionCounterOfType('Like')->getCount();

            }
        }
    }

    public function voteToggle(VoteToggle $action)
    {
        $user = auth()->user();
        $model = Media::find($this->id);

        if (!$model) return;

        $action->handle($model);

        // Refresh vote status
        $reactantFacade = $model->viaLoveReactant();
        $this->isVoted = $reactantFacade->isReactedBy($user, 'Like');
        $this->voteCount = $reactantFacade->getReactionCounterOfType('Like')->getCount();
        $this->dispatch('$refresh');
    }



} ?>


<div>

                            @volt('card-image')
                        <div class="p-3 transition-transform duration-300 bg-white border shadow-sm cursor-pointer group dark:bg-gray-900 rounded-xl hover:-translate-y-1 hover:shadow-xl border-slate-100 dark:border-white/10">
                            <img src="{{ $image }}" class="object-cover w-full  lg:h-[280px] rounded-md" />
                            <span class="flex flex-col justify-start px-1 py-2">
                                <span class="line-clamp-1 pt-2.5 text-lg font-semibold flex items-center dark:text-white space-x-1.5">
                                    <span>{{ $title }}</span>
                                </span>
                                 <p class="block pt-1.5 pb-3 text-sm text-left line-clamp-2 text-slate-800/60 dark:text-white/50">
                                    {{ $description }}
                                </p>
            <a href="{{ $detailsUrl }}" class="px-4 py-2 text-sm font-medium rounded-md bg-white border text-gray-500 hover:text-gray-700 border-gray-200/70 dark:focus:ring-offset-gray-900 dark:border-gray-400/10 hover:bg-gray-50 active:bg-white dark:focus:ring-gray-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200/60 dark:bg-gray-800/50 dark:hover:bg-gray-800/70 dark:text-gray-400 focus:shadow-outline cursor-pointer inline-flex items-center w-full justify-center disabled:opacity-50 font-semibold focus:outline-none"> View Details </a>

            <div class="mt-2.5 flex items-center space-x-1.5">

                            </span>
                        </div>


    <div class="flex space-x-2" x-data="{ isVoted: @entangle('isVoted'), voteCount: @entangle('voteCount') }">
    <div x-text="voteCount"></div>
    <button
        wire:click="voteToggle"
        x-on:click="isVoted = !isVoted"
    >
        <span x-show="!isVoted"><svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="#000000"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-thumb-up"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 11v8a1 1 0 0 1 -1 1h-2a1 1 0 0 1 -1 -1v-7a1 1 0 0 1 1 -1h3a4 4 0 0 0 4 -4v-1a2 2 0 0 1 4 0v5h3a2 2 0 0 1 2 2l-1 5a2 3 0 0 1 -2 2h-7a3 3 0 0 1 -3 -3" /></svg></span>
        <span x-show="isVoted"><svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="#000000"  class="icon icon-tabler icons-tabler-filled icon-tabler-thumb-up"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M13 3a3 3 0 0 1 2.995 2.824l.005 .176v4h2a3 3 0 0 1 2.98 2.65l.015 .174l.005 .176l-.02 .196l-1.006 5.032c-.381 1.626 -1.502 2.796 -2.81 2.78l-.164 -.008h-8a1 1 0 0 1 -.993 -.883l-.007 -.117l.001 -9.536a1 1 0 0 1 .5 -.865a2.998 2.998 0 0 0 1.492 -2.397l.007 -.202v-1a3 3 0 0 1 3 -3z" /><path d="M5 10a1 1 0 0 1 .993 .883l.007 .117v9a1 1 0 0 1 -.883 .993l-.117 .007h-1a2 2 0 0 1 -1.995 -1.85l-.005 -.15v-7a2 2 0 0 1 1.85 -1.995l.15 -.005h1z" /></svg></span>
    </button>
</div>

            @endvolt
</div>
