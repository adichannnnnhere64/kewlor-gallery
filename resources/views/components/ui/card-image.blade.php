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

        // Refresh vote status
        $reactantFacade = $model->viaLoveReactant();
        $this->isVoted = $reactantFacade->isReactedBy($user, 'Like');
        $this->voteCount = $reactantFacade->getReactions()->count();
        $this->dispatch('$refresh');
    }
}
?>

@volt('card-image')
    <div>
        <div class="p-3 transition-transform duration-300 bg-white border shadow-sm cursor-pointer group dark:bg-gray-900 rounded-xl hover:-translate-y-1 hover:shadow-xl border-slate-100 dark:border-white/10">
            <img src="{{ $image }}" class="object-cover w-full lg:h-[280px] rounded-md" />
            <span class="flex flex-col justify-start px-1 py-2">
                <span class="line-clamp-1 pt-2.5 text-lg font-semibold flex items-center dark:text-white space-x-1.5">
                    <span>{{ $title }}</span>
                </span>
                <p class="block pt-1.5 pb-3 text-sm text-left line-clamp-2 text-slate-800/60 dark:text-white/50">
                    {{ $description }}
                </p>
                <a href="{{ $detailsUrl }}" class="px-4 py-2 text-sm font-medium rounded-md bg-white border text-gray-500 hover:text-gray-700 border-gray-200/70 dark:focus:ring-offset-gray-900 dark:border-gray-400/10 hover:bg-gray-50 active:bg-white dark:focus:ring-gray-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200/60 dark:bg-gray-800/50 dark:hover:bg-gray-800/70 dark:text-gray-400 focus:shadow-outline cursor-pointer inline-flex items-center w-full justify-center disabled:opacity-50 font-semibold focus:outline-none">
                    View Details
                </a>

                <div class="mt-2.5 flex items-center space-x-1.5">
                </div>
            </span>

        <div class="flex justify-end">
        <x-ui.like-toggle :id="$id"/>
</div>

        </div>


    </div>
@endvolt
