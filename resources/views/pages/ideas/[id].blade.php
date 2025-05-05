<?php

use function Laravel\Folio\{middleware, name};
use Livewire\Volt\Component;
use App\Models\LiveEventGallery;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use App\Models\Category;
use App\Actions\VoteToggle;

name('ideas.show');
middleware(['auth', 'verified', 'can:access-admin-panel']);

new class extends Component {
    use WithPagination;

    #[Url]
    public $sortBy = 'newest';

    public $id;
    public $category;

    public function mount()
    {
        $this->category = Category::find($this->id);
    }

    public function deleteLiveEvent(int $id)
    {
        $liveEvent = LiveEventGallery::find($id);
        if ($liveEvent) {
            $liveEvent->delete();
        }

        return redirect()->route('ideas.show', ['id' => $this->id]);
    }

    #[Computed]
    public function liveEvents()
    {
        $this->category = Category::find($this->id);

        $bargo = LiveEventGallery::query()
            ->whereHas('categories', function ($query) {
                $query->where('id', $this->id);
            })
            ->with('media')
            ->when($this->sortBy === 'newest', function ($query) {
                return $query->reorder()->orderBy('created_at', 'desc');
            })
            ->when($this->sortBy === 'oldest', function ($query) {
                return $query->reorder()->orderBy('created_at', 'asc');
            })
            ->reorder()
            ->orderBy('order_column')

            ->paginate(20);

        return $bargo;
    }

    public function fetchEvents()
    {
        $this->category = Category::find($this->id);

        $bargo = LiveEventGallery::query()
            ->whereHas('categories', function ($query) {
                $query->where('id', $this->id);
            })
            ->with('media')
            ->when($this->sortBy === 'newest', function ($query) {
                return $query->reorder()->orderBy('created_at', 'desc');
            })
            ->when($this->sortBy === 'oldest', function ($query) {
                return $query->reorder()->orderBy('created_at', 'asc');
            })
            ->reorder()
            ->orderBy('order_column')

            ->paginate(20);

        $this->liveEvents = $bargo;
    }

    public function like(LiveEventGallery $model, VoteToggle $action)
    {
        $action->handle($model, 'like');
    }

    public function dislike(LiveEventGallery $model, VoteToggle $action)
    {
        $action->handle($model, 'dislike');
    }

    public function updateOrder(LiveEventGallery $model, $item)
    {
        $originalIds = collect($this->liveEvents->items())->pluck('id');
        $updated = $originalIds->reject(fn($id) => $id == $model->id);
        $updated->splice($item, 0, [$model->id]);
        LiveEventGallery::setNewOrder($updated->toArray());
        $this->fetchEvents();
    }
};

?>

<x-layouts.app>



    @volt('ideas.show')

        <div x-data="{ handle: (item, position) => $wire.updateOrder(item, position) }">
            <div class="flex max-w-6xl mx-auto justify-between items-center  py-4">
                <div class="w-20">
                    <x-ui.button tag="a" type="secondary" href="{{ route('gallery-index') }}" class="mb-8 inline">
                        <div class="flex items-center space-x-1 ">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="icon icon-tabler icons-tabler-outline icon-tabler-arrow-left">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M5 12l14 0" />
                                <path d="M5 12l6 6" />
                                <path d="M5 12l6 -6" />
                            </svg>

                            <span>Back</span>
                        </div>
                    </x-ui.button>
                </div>

                <div class="">

                </div>
            </div>



            <div class="flex justify-between items-center max-w-6xl mx-auto ">
                <h1 class="font-bold text-primary-700 text-2xl">{{ $category->name }}</h1>

            </div>

            @livewire('wire-elements-modal')

            <div class="flex md:flex-row space-x-4 flex-col space-y-2  justify-between items-start ">
                <div class="w-full">
                    <p class="block pt-1.5 pb-3 text-xs text-left line-clamp-2 text-slate-800/60 dark:text-white/50">
                        {{ $category->description }}</p>
                </div>
                <div class=" flex justify-end space-x-2 h-10 w-[300px]">
                    <button class="bg-primary-700 hover:bg-primary-800 text-white font-bold py-2 px-4 rounded"
                        wire:click="$dispatch('openModal', { component: 'modals.create-idea', arguments: { categoryId: {{ $id }} } })">
                        + Add Concept
                    </button>
                    <a class="bg-orange-700 hover:bg-orange-800 text-white font-bold py-2 px-4 rounded" target="_blank"
                        href="{{ route('category.edit', ['id' => $id]) }}">Edit </a>

                </div>
            </div>


            <div class="max-w-6xl mx-auto mb-10">




                @if (isset($this->liveEvents) && $this->liveEvents->isNotEmpty())
                    <div x-sort="handle" x-on:sorted="$wire.updateOrder($event)"
                        class="grid w-full lg:grid-cols-5 sm:grid-cols-2 gap-2 mt-8 max-w-6xl ">

                        @foreach ($this->liveEvents as $key => $liveEvent)
                            <div x-sort:item="{{ $liveEvent->id }}" wire:key="key-{{ $liveEvent->id }}">
                                <div class="relative group">
                                    <div
                                        class="absolute z-20 top-0  group-hover:opacity-50 opacity-0 right-0 transition-opacity">


                                        <button wire:key="edit-btn-{{ $liveEvent->id }}" x-data="{ showAlert: false }"
                                            @idea-updated.window="$wire.$refresh()"
                                            class="px-1 py-1 rounded-md text-white bg-primary-700  group-hover:opacity-30 hover:group-hover:opacity-100 transition-opacity"
                                            wire:click="$dispatch('openModal', { component: 'modals.edit-idea', arguments: { liveEvent: {{ $liveEvent }} } })">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="icon icon-tabler icons-tabler-outline icon-tabler-edit">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" />
                                                <path
                                                    d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" />
                                                <path d="M16 5l3 3" />
                                            </svg>
                                        </button>
                                        <div>
                                            <button wire:key="delete-btn-{{ $liveEvent->id }}" x-data
                                                @click="confirm('Are you sure you want to delete this item?') && $wire.deleteLiveEvent({{ $liveEvent->id }})"
                                                class="px-1 py-1 rounded-md text-white bg-red-700 group-hover:opacity-30 hover:group-hover:opacity-100 transition-opacity">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                    class="icon icon-tabler icons-tabler-outline icon-tabler-trash">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path d="M4 7l16 0" />
                                                    <path d="M10 11l0 6" />
                                                    <path d="M14 11l0 6" />
                                                    <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                                    <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <x-ui.card :sortBy="$sortBy" :liveEventId="$liveEvent->id"
                                        wire:key="img-{{ $liveEvent->id }}-{{ $sortBy }}-{{ $key }}"
                                        :showComment="true" :key="$liveEvent->id" :title="$liveEvent->name" :description="$liveEvent->date"
                                        :likesCount="$liveEvent->likes_count" :dislikesCount="$liveEvent->dislikes_count" :commentsCount="$liveEvent->comments_count" :id="$liveEvent->id"
                                        :image="$liveEvent
                                            ->getMedia('default')
                                            ->sortBy('order_column')
                                            ->first()
                                            ?->findVariant('thumbnail')
                                            ?->getUrl() ??
                                            $liveEvent->getMedia('default')->sortBy('order_column')->first()
                                                ?->video_thumbnail" :detailsUrl="route('live-event.show', ['id' => $liveEvent->id])" />
                                </div>
                            </div>
                        @endforeach



                    </div>
                    @if ($this?->liveEvents?->hasPages())
                        <div class="mt-4 ">
                            {{ $this->liveEvents->links() }}
                        </div>
                    @endif
                @endif


                <div class="comments">
                    <x-notes.note :model="$this->category" />
                </div>
            </div>
        </div>

    @endvolt


</x-layouts.app>
