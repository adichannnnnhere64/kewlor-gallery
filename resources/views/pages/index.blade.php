<?php

use function Laravel\Folio\{middleware, name};
use Livewire\Volt\Component;
use App\Models\Category;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

name('home');
middleware(['auth', 'verified', 'can:access-admin-panel']);

new class extends Component {
    use WithPagination;

    #[Url]
    public $sortBy = 'newest';

    #[Computed]
    public function categories()
    {
        $bargo = Category::query()->reorder()->orderBy('order_column')->paginate(20);

        return $bargo;
    }

    public function updateOrder(Category $category, $item)
    {
        $originalIds = collect($this->categories->items())->pluck('id');
        $updated = $originalIds->reject(fn($id) => $id == $category->id);
        $updated->splice($item, 0, [$category->id]);
        Category::setNewOrder($updated->toArray());
        $this->fetchCategories();
    }

    public function fetchCategories()
    {
        $this->categories =  Category::query()->reorder()->orderBy('order_column')->paginate(20);
    }
};

?>

<x-layouts.app>



    @volt('home')

        <div x-data="{ handle: (item, position) => $wire.updateOrder(item, position) }">
            <div class="flex justify-between items-center max-w-6xl mx-auto ">
                <h1 class="  mt-8 font-bold text-primary-700 text-2xl">Categories</h1>
            </div>

            <div class="max-w-6xl mx-auto mb-10">
                @if (isset($this->categories) && $this->categories->isNotEmpty())

            <div
                x-sort="handle" x-on:sorted="$wire.updateOrder($event)"
                class="grid w-full lg:grid-cols-5 sm:grid-cols-2 gap-2 mt-8 max-w-6xl ">
                        @foreach ($this->categories as $key => $liveEvent)
                            <div x-sort:item="{{ $liveEvent->id }}">
                                <x-ui.card :sortBy="$sortBy" :liveEventId="$liveEvent->id"
                                    wire:key="img-{{ $liveEvent->id }}-{{ $sortBy }}-{{ $key }}"
                                    :showComment="false" :key="$liveEvent->id" :title="$liveEvent->name" :description="$liveEvent->date"
                                    :image="$liveEvent->image" :detailsUrl="route('ideas.show', ['id' => $liveEvent->id])" />
                            </div>
                        @endforeach



                    </div>
                    @if ($this?->categories?->hasPages())
                        <div class="mt-4 px-8">
                            {{ $this->categories->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>

    @endvolt


</x-layouts.app>
