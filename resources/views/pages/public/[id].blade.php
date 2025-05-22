<?php

use Illuminate\Support\Facades\Http;
use function Laravel\Folio\{middleware, name};
use function Livewire\Volt\{state, with};
use Livewire\Volt\Component;
use App\Models\LiveEventGallery;
use App\Models\Media;
use App\Models\MediaGroup;
use App\Models\LiveEventGalleryMediaGroup;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Attributes\Reactive;
use Livewire\Attributes\On;

name('live-event.show');
middleware(['auth', 'verified', 'can:access-admin-panel']);

new class extends Component {
    use WithPagination;

    public $id;
    public $name;
    public $date;
    public $description;

    public $liveEvent;
    #[Url]
    public $type = 'all';

    public function mount()
    {
        $this->liveEvent = LiveEventGallery::findOrFail($this->id);

        $this->name = $this->liveEvent->name;
        $this->description = $this->liveEvent->description;
    }

    #[On('idea-updated')]
    public function updatePostList($liveEvent)
    {
        $this->name = $liveEvent['liveEvent']['name'];
        $this->description = $liveEvent['liveEvent']['description'];
    }

    #[On('upload-complete')]
    public function uploadComplete($liveEvent)
    {
        $this->dispatch('$refresh');
    }

    public function fetchImages()
    {
        $liveEvent = LiveEventGallery::findOrFail($this->id);
        $this->images = $liveEvent->media()->withLikeCounts()->withPivot('tag')->where('is_approved', 0)->where('tag', 'default')->reorder()->orderBy('order_column')->paginate(20);

        $this->media_groups = $this->media_groups();
    }

    #[Computed]
    public function images()
    {
        $liveEvent = LiveEventGallery::findOrFail($this->id);

        $bargo = $liveEvent
            ->media()
            ->withLikeCounts()
            ->withPivot('tag')
            ->where('tag', 'default')
            ->reorder()
            ->when($this->type && $this->type != 'all', function ($query) {
                $query->where('aggregate_type', $this->type);
            })
            ->orderBy('order_column')
            ->paginate(20);

        return $bargo;
    }

    #[Computed]
    public function media_groups()
    {
        $liveEvent = LiveEventGallery::findOrFail($this->id);
        $groups = MediaGroup::query()->get();

        // Load media for each group
        foreach ($groups as $group) {
            $group->media = $group
                ->media()
                ->whereIn('id', $liveEvent->media()->pluck('id')->toArray())
                ->withLikeCounts()
                ->when($this->type && $this->type != 'all', function ($query) {
                    $query->where('aggregate_type', $this->type);
                })
                ->reorder()
                ->orderBy('pivot_order_column')
                ->get()->unique('id');
        }

        return $groups;
    }

    #[Computed]
    public function approvedImages()
    {
        $liveEvent = LiveEventGallery::findOrFail($this->id);

        $bargo = $liveEvent
            ->media()
            ->withLikeCounts()
            ->withPivot('tag')
            ->where('tag', 'default')
            ->reorder()
            ->when($this->type && $this->type != 'all', function ($query) {
                $query->where('aggregate_type', $this->type);
            })
            ->where('is_approved', 1)
            ->orderBy('order_column')
            ->paginate(20);

        return $bargo;
    }

    #[On('refresh')]
    public function refresh()
    {
        $this->dispatch('$refresh');
    }

    public function deleteImage(int $id)
    {
        Media::find($id)->media_groups()->detach();
        Media::find($id)->delete();
    }

    public function updateOrder($payload, $item, $groupId = null)
    {

        // Extract image ID and source group ID from payload
        $imageId = $payload[0];
        $sourceGroupId = $payload[1] ?? null;
        $targetGroupId = $groupId ?? $sourceGroupId;

        // Get the media item
        $media = Media::find($imageId);

        if (!$media) {
            return;
        }

        try {
            // If moving between groups
            if ($sourceGroupId != $targetGroupId && $targetGroupId) {
                // Get the live event
                $liveEvent = LiveEventGallery::findOrFail($this->id);

                // Detach from old group if needed
                if ($sourceGroupId) {
                    $media->media_groups()->detach($sourceGroupId);
                }

                // Get the target group
                $targetGroup = MediaGroup::find($targetGroupId);

                // Handle special case for adding to end of group
                if ($item >= 999) {
                    // Get the count of media in the target group to add at the end
                    $mediaCount = $targetGroup->media()->count();
                    $media->media_groups()->attach($targetGroupId, ['order_column' => $mediaCount + 1]);
                } else {
                    // Attach to new group with proper order at the specified position
                    $targetMedia = $targetGroup->media()->orderBy('pivot_order_column')->get();
                    $mediaIds = $targetMedia->pluck('id')->toArray();

                    // Insert the new media ID at the specified position
                    array_splice($mediaIds, $item, 0, [$imageId]);

                    // Attach with initial order
                    $media->media_groups()->attach($targetGroupId);

                    // Update the order of all items in the target group
                    foreach ($mediaIds as $index => $id) {
                        \DB::table('media_group_media')
                            ->where('media_group_id', $targetGroupId)
                            ->where('media_id', $id)
                            ->update(['order_column' => $index + 1]);
                    }
                }
            } else {
                // Update order within the same group
                $group = MediaGroup::find($sourceGroupId);

                // Get all media in the current group with proper ordering
                $allMedia = $group->media()->reorder()->orderBy('pivot_order_column')->get();
                $originalIds = $allMedia->pluck('id')->toArray();

                // Remove the dragged item from the array
                $updated = array_values(
                    array_filter($originalIds, function ($id) use ($imageId) {
                        return $id != $imageId;
                    }),
                );

                // Insert the dragged item at the new position
                array_splice($updated, $item, 0, [$imageId]);

                // Update the order of all items in the group
                foreach ($updated as $index => $id) {
                    \DB::table('media_media_group')
                        ->where('media_group_id', $sourceGroupId)
                        ->where('media_id', $id)
                        ->update(['order_column' => $index + 1]);
                }
            }

            // Update the media_groups property in the background
            // We don't need to refresh the UI since we've already updated it optimistically
            $this->media_groups = $this->media_groups();

            return [
                'success' => true,
                'sourceGroupId' => $sourceGroupId,
                'targetGroupId' => $targetGroupId,
                'mediaId' => $imageId,
            ];
        } catch (\Exception $e) {
            // If there's an error, we should refresh to get the correct state
            $this->fetchImages();
            $this->dispatch('$refresh');

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

};
?>

<style>
    .dark img[alt="Kewlor Logo"] {
        filter: invert(1);
    }

    .lightbox-overlay {
        /* Your existing lightbox styles */
    }
</style>

<x-layouts.app>
    @volt('live-event.show')
        <div>

            @livewire('wire-elements-modal')
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


            <div class="mx-auto max-w-6xl">

                <div class="flex md:flex-row flex-col space-y-2 justify-between items-center">

                    <div class="mr-8">
                        <h1 class=" font-bold text-primary-700 text-2xl">{{ $name }}</h1>
                        <x-ui.description>
                            {{ $description }}
                        </x-ui.description>
                    </div>
                    <div class="flex flex-col  space-x-2 space-y-4">
                        <div class="flex space-x-2 items-center">
                            <button class="w-34 bg-primary-700 hover:bg-primary-800 text-white font-bold py-2 px-4 rounded"
                                wire:click="$dispatch('openModal', { component: 'modals.add-image-in-live-event', arguments: { liveEventId: {{ $id }} } })">
                                + Add Image
                            </button>
                            <a class="bg-orange-700 hover:bg-orange-800 text-white font-bold py-2 px-4 rounded"
                                target="_blank" href="{{ route('live-event.edit', ['id' => $id]) }}">Edit </a>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end mt-2">
                    <div class="h-4">
                        Filter by:
                        <select wire:model.live="type"
                            class="border border-gray-300 dark:text-white -mt-10  rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="all">All</option>
                            <option value="image">Image</option>
                            <option value="video">Video</option>
                            <option value="audio">Audio</option>
                        </select>
                    </div>
                </div>

                <div x-data="{
                    activeAccordions: [
                        @foreach ($this->media_groups as $index => $group){{ $index }}{{ !$loop->last ? ',' : '' }} @endforeach
                    ],
                    draggedItem: null,
                    draggedFromGroup: null
                }" class="max-w-6xl mx-auto my-12">

                    <div class="overflow-hidden divide-y" x-data="{
                        handle: (item, position, groupId) => $wire.updateOrder(item, position, groupId),
                        initSort: function() {
                            // Setup sortable for each group container
                            document.querySelectorAll('[data-group-id]').forEach(container => {
                                // Track positions within group
                                let positions = [];
                                let dragging = null;
                                let targetIndex = -1;
                                let sourceGroupId = null;
                                let targetGroupId = null;

                                // Make all image items draggable
                                container.querySelectorAll('[x-sort\\:item]').forEach(item => {
                                    item.setAttribute('draggable', 'true');

                                    // Store initial positions
                                    const itemData = JSON.parse(item.getAttribute('x-sort:item'));
                                    positions.push({
                                        id: itemData[0],
                                        groupId: itemData[1],
                                        element: item
                                    });

                                    // Initialize global variables for drag and drop
                                    if (typeof window.dragging === 'undefined') {
                                        window.dragging = null;
                                        window.draggedItem = null;
                                        window.sourceGroupId = null;
                                    }

                                    // Drag start
                                    item.addEventListener('dragstart', function(e) {
                                        // Clear any previous dragging state
                                        document.querySelectorAll('[x-sort\\:item]').forEach(el => {
                                            el.classList.remove('opacity-50');
                                        });

                                        window.dragging = item;
                                        const itemData = JSON.parse(this.getAttribute('x-sort:item'));
                                        window.draggedItem = itemData;
                                        window.sourceGroupId = itemData[1];
                                        this.classList.add('opacity-50');
                                        e.dataTransfer.effectAllowed = 'move';
                                        e.dataTransfer.setData('text/plain', JSON.stringify(itemData));
                                        e.stopPropagation();
                                    });

                                    // Drag end
                                    item.addEventListener('dragend', function() {
                                        this.classList.remove('opacity-50');

                                        // Reset all item styles
                                        document.querySelectorAll('[x-sort\\:item]').forEach(el => {
                                            el.classList.remove('border-t-2', 'border-primary-500');
                                        });

                                        // Clear dragging state with a small delay to allow drop handlers to complete
                                        setTimeout(() => {
                                            window.dragging = null;
                                            window.draggedItem = null;
                                        }, 50);
                                    });

                                    // Drag over
                                    item.addEventListener('dragover', function(e) {
                                        e.preventDefault();
                                        e.dataTransfer.dropEffect = 'move';

                                        if (window.dragging !== this) {
                                            // Clear previous indicators
                                            document.querySelectorAll('[x-sort\\:item]').forEach(el => {
                                                el.classList.remove('border-t-2', 'border-primary-500');
                                            });

                                            // Show drop indicator
                                            this.classList.add('border-t-2', 'border-primary-500');
                                            targetIndex = Array.from(container.querySelectorAll('[x-sort\\:item]')).indexOf(this);
                                        }
                                    });

                                    // Drop
                                    item.addEventListener('drop', function(e) {
                                        e.preventDefault();
                                        e.stopPropagation();

                                        if (window.dragging) {
                                            const itemData = JSON.parse(this.getAttribute('x-sort:item'));
                                            targetGroupId = itemData[1];
                                            const draggedData = JSON.parse(e.dataTransfer.getData('text/plain'));

                                            // Get position in the target group
                                            const position = Array.from(container.querySelectorAll('[x-sort\\:item]')).indexOf(this);

                                            // Optimistic UI update - move the element immediately
                                            if (window.sourceGroupId === targetGroupId) {
                                                // Same group - reorder
                                                const parent = window.dragging.parentNode;
                                                const draggedIndex = Array.from(parent.children).indexOf(window.dragging);

                                                if (position > draggedIndex) {
                                                    // Moving down
                                                    parent.insertBefore(window.dragging, this.nextSibling);
                                                } else {
                                                    // Moving up
                                                    parent.insertBefore(window.dragging, this);
                                                }
                                            } else {
                                                // Different group - move between groups
                                                const sourceContainer = window.dragging.parentNode;
                                                const targetContainer = this.parentNode;

                                                // Create a reference to the dragging variable for closures
                                                let currentDragging = window.dragging;

                                                // Clone the dragged element to avoid DOM issues when moving between groups
                                                const clone = window.dragging.cloneNode(true);

                                                // Update the data attribute for the new group
                                                const itemData = JSON.parse(clone.getAttribute('x-sort:item'));
                                                itemData[1] = targetGroupId; // Update the group ID
                                                clone.setAttribute('x-sort:item', JSON.stringify(itemData));

                                                // Remove from source
                                                sourceContainer.removeChild(window.dragging);

                                                // Add to target
                                                if (position >= targetContainer.children.length) {
                                                    targetContainer.appendChild(clone);
                                                } else {
                                                    targetContainer.insertBefore(clone, this);
                                                }

                                                // Make the clone draggable again
                                                clone.setAttribute('draggable', 'true');

                                                // Add event listeners to the clone
                                                clone.addEventListener('dragstart', function(e) {
                                                    // Clear any previous dragging state
                                                    document.querySelectorAll('[x-sort\\:item]').forEach(el => {
                                                        el.classList.remove('opacity-50');
                                                    });

                                                    // Use the global dragging variable
                                                    window.dragging = clone;
                                                    const cloneData = JSON.parse(this.getAttribute('x-sort:item'));
                                                    window.draggedItem = cloneData;
                                                    window.sourceGroupId = cloneData[1];
                                                    this.classList.add('opacity-50');
                                                    e.dataTransfer.effectAllowed = 'move';
                                                    e.dataTransfer.setData('text/plain', JSON.stringify(cloneData));
                                                    e.stopPropagation();
                                                });

                                                clone.addEventListener('dragend', function() {
                                                    this.classList.remove('opacity-50');
                                                    document.querySelectorAll('[x-sort\\:item]').forEach(el => {
                                                        el.classList.remove('border-t-2', 'border-primary-500');
                                                    });

                                                    // Clear dragging state with a small delay
                                                    setTimeout(() => {
                                                        window.dragging = null;
                                                        window.draggedItem = null;
                                                    }, 50);
                                                });

                                                clone.addEventListener('dragover', function(e) {
                                                    e.preventDefault();
                                                    e.dataTransfer.dropEffect = 'move';

                                                    if (window.dragging !== this) {
                                                        document.querySelectorAll('[x-sort\\:item]').forEach(el => {
                                                            el.classList.remove('border-t-2', 'border-primary-500');
                                                        });
                                                        this.classList.add('border-t-2', 'border-primary-500');
                                                    }
                                                });

                                                clone.addEventListener('drop', function(e) {
                                                    e.preventDefault();
                                                    e.stopPropagation();
                                                    if (window.dragging) {
                                                        const dropData = JSON.parse(this.getAttribute('x-sort:item'));
                                                        const dropGroupId = dropData[1];
                                                        const dropDraggedData = JSON.parse(e.dataTransfer.getData('text/plain'));
                                                        const dropPosition = Array.from(this.parentNode.querySelectorAll('[x-sort\\:item]')).indexOf(this);
                                                        $wire.updateOrder(dropDraggedData, dropPosition, dropGroupId);
                                                    }
                                                });
                                            }

                                            // Update order with the correct data
                                            $wire.updateOrder(draggedData, position, targetGroupId);
                                        }
                                    });
                                });

                                // Prevent dragging on parent elements
                                container.closest('.bg-whitey.dark\\:bg-gray-900').setAttribute('draggable', 'false');
                            });

                            // Prevent dragging on group headers
                            document.querySelectorAll('.bg-whitey.dark\\:bg-gray-900').forEach(group => {
                                group.setAttribute('draggable', 'false');

                                group.addEventListener('dragstart', function(e) {
                                    if (e.target.hasAttribute('x-sort:item')) return;
                                    e.preventDefault();
                                    e.stopPropagation();
                                });
                            });

                            // Make empty group containers drop targets
                            document.querySelectorAll('[data-group-id]').forEach(container => {
                                // Only add drop handlers if the container is empty or for the container itself
                                container.addEventListener('dragover', function(e) {
                                    e.preventDefault();

                                    // If we're dragging over the container itself (not an item)
                                    if (e.target === this || !e.target.hasAttribute('x-sort:item')) {
                                        e.dataTransfer.dropEffect = 'move';
                                        this.classList.add('bg-gray-100', 'dark:bg-gray-800', 'border-2', 'border-dashed', 'border-primary-500');
                                    }
                                });

                                container.addEventListener('dragleave', function(e) {
                                    // Only remove styles if we're leaving the container itself
                                    if (e.target === this) {
                                        this.classList.remove('bg-gray-100', 'dark:bg-gray-800', 'border-2', 'border-dashed', 'border-primary-500');
                                    }
                                });

                                container.addEventListener('drop', function(e) {
                                    // Only handle drop if we're dropping on the container itself (not an item)
                                    if (e.target === this || !e.target.hasAttribute('x-sort:item')) {
                                        e.preventDefault();
                                        this.classList.remove('bg-gray-100', 'dark:bg-gray-800', 'border-2', 'border-dashed', 'border-primary-500');

                                        try {
                                            const draggedData = e.dataTransfer.getData('text/plain');
                                            if (draggedData && dragging) {
                                                const parsedData = JSON.parse(draggedData);
                                                const targetGroupId = parseInt(this.dataset.groupId);
                                                const sourceGroupId = parsedData[1];
                                                const imageId = parsedData[0];

                                                // Optimistic UI update - move the element immediately
                                                if (window.dragging) {
                                                    const sourceContainer = window.dragging.parentNode;

                                                    // Clone the dragged element to avoid DOM issues when moving between groups
                                                    const clone = window.dragging.cloneNode(true);

                                                    // Update the data attribute for the new group
                                                    const itemData = JSON.parse(clone.getAttribute('x-sort:item'));
                                                    itemData[1] = targetGroupId; // Update the group ID
                                                    clone.setAttribute('x-sort:item', JSON.stringify(itemData));

                                                    // Remove from source
                                                    sourceContainer.removeChild(window.dragging);

                                                    // Add to target (empty container)
                                                    this.appendChild(clone);

                                                    // Make the clone draggable again
                                                    clone.setAttribute('draggable', 'true');

                                                    // Add event listeners to the clone
                                                    clone.addEventListener('dragstart', function(e) {
                                                        // Clear any previous dragging state
                                                        document.querySelectorAll('[x-sort\\:item]').forEach(el => {
                                                            el.classList.remove('opacity-50');
                                                        });

                                                        window.dragging = clone;
                                                        const cloneData = JSON.parse(this.getAttribute('x-sort:item'));
                                                        window.draggedItem = cloneData;
                                                        window.sourceGroupId = cloneData[1];
                                                        this.classList.add('opacity-50');
                                                        e.dataTransfer.effectAllowed = 'move';
                                                        e.dataTransfer.setData('text/plain', JSON.stringify(cloneData));
                                                        e.stopPropagation();
                                                    });

                                                    clone.addEventListener('dragend', function() {
                                                        this.classList.remove('opacity-50');
                                                        document.querySelectorAll('[x-sort\\:item]').forEach(el => {
                                                            el.classList.remove('border-t-2', 'border-primary-500');
                                                        });

                                                        // Clear dragging state with a small delay
                                                        setTimeout(() => {
                                                            window.dragging = null;
                                                            window.draggedItem = null;
                                                        }, 50);
                                                    });

                                                    clone.addEventListener('dragover', function(e) {
                                                        e.preventDefault();
                                                        e.dataTransfer.dropEffect = 'move';

                                                        if (window.dragging !== this) {
                                                            document.querySelectorAll('[x-sort\\:item]').forEach(el => {
                                                                el.classList.remove('border-t-2', 'border-primary-500');
                                                            });
                                                            this.classList.add('border-t-2', 'border-primary-500');
                                                        }
                                                    });

                                                    clone.addEventListener('drop', function(e) {
                                                        e.preventDefault();
                                                        e.stopPropagation();
                                                        if (window.dragging) {
                                                            const dropData = JSON.parse(this.getAttribute('x-sort:item'));
                                                            const dropGroupId = dropData[1];
                                                            const dropDraggedData = JSON.parse(e.dataTransfer.getData('text/plain'));
                                                            const dropPosition = Array.from(this.parentNode.querySelectorAll('[x-sort\\:item]')).indexOf(this);
                                                            $wire.updateOrder(dropDraggedData, dropPosition, dropGroupId);
                                                        }
                                                    });
                                                }

                                                // Add to the end of the group
                                                $wire.updateOrder([imageId, sourceGroupId], 999, targetGroupId);
                                            }
                                        } catch (error) {
                                            console.error('Error processing drop:', error);
                                        }
                                    }
                                });
                            });
                        }
                    }" x-init="initSort()" wire:ignore>
                        <div className="">
                            @foreach ($this->media_groups as $index => $group)
                                <div wire:key="group-{{ $group->id }}" x-data="{ id: {{ $index }} }"
                                    class="bg-whitey dark:bg-gray-900">

                                    <div class="flex justify-between items-center w-full py-3">
                                        <button
                                            @click.stop="activeAccordions.includes(id) ? activeAccordions = activeAccordions.filter(i => i !== id) : activeAccordions.push(id)"
                                            class="flex justify-between items-center flex-grow text-left">
                                            <span class=" font-bold text-primary-700 text-md">{{ $group->name }}</span>
                                            <svg :class="activeAccordions.includes(id) ? 'rotate-180 transform' : ''"
                                                class="w-5 h-5 transition-transform duration-200"
                                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <polyline points="6 9 12 15 18 9"></polyline>
                                            </svg>
                                        </button>
                                    </div>

                                    <div x-show="activeAccordions.includes(id)"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 transform scale-95"
                                        x-transition:enter-end="opacity-100 transform scale-100"
                                        x-transition:leave="transition ease-in duration-100"
                                        x-transition:leave-start="opacity-100 transform scale-100"
                                        x-transition:leave-end="opacity-0 transform scale-95">
                                        @if ($group->media->count() == 0)
                                            <div class="text-center grid-cols-2 text-gray-500 text-sm">No images. Drag and
                                                drop here.</div>
                                        @endif

                                        <div class="grid grid-cols-5  w-full min-h-[100px] transition-all duration-200"
                                            data-group-id="{{ $group->id }}">


                                            @foreach ($group->media ?? [] as $key => $image)
                                                <div x-sort:item="[{{ $image->id }}, {{ $group->id }}]"
                                                    class="relative cursor-move border-transparent"
                                                    wire:key="grid-{{ $image->id }}-{{ $group->id }}">
                                                    <div class="relative group"">

                                                        <div
                                                            class="absolute z-50 top-0 flex flex-col space-y-1  group-hover:opacity-50 opacity-0 right-0 transition-opacity">
                                                            <button x-data
                                                                @click="confirm('Are you sure you want to delete this item?') && $wire.deleteImage({{ $image->id }})"
                                                                class="px-1 py-1 rounded-md text-white bg-red-700 group-hover:opacity-30 hover:group-hover:opacity-100 transition-opacity">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                    height="24" viewBox="0 0 24 24" fill="none"
                                                                    stroke="currentColor" stroke-width="2"
                                                                    stroke-linecap="round" stroke-linejoin="round"
                                                                    class="icon icon-tabler icons-tabler-outline icon-tabler-trash">
                                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                    <path d="M4 7l16 0" />
                                                                    <path d="M10 11l0 6" />
                                                                    <path d="M14 11l0 6" />
                                                                    <path
                                                                        d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                                                    <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                                                                </svg>
                                                            </button>



                                                        </div>

                                                        <x-ui.card-image :model="$image" :liveEventId="$id"
                                                            :sortBy="$type" :likesCount="$image->likes_count" :currentVote="$image->current_vote"
                                                            :dislikesCount="$image->dislikes_count" :key="$image->id" :id="$image->id"
                                                            :image="$image?->findVariant('thumbnail')?->getUrl() ??
                                                                $image?->video_thumbnail" :showComment="true" :description="$date"
                                                            :detailsUrl="route('public.image.show', ['id' => $image->id])" />

                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="mt-2 flex justify-end">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>




                @if ($this?->images?->hasPages())
                    <div class="mt-4 ">
                        {{ $this->images->links() }}
                    </div>
                @endif

                <br />
                <hr />

                <div class="my-4"></div>

                <div class="comments">
                    <x-notes.note :model="$this->liveEvent" />
                </div>
            </div>

        </div>
    @endvolt
</x-layouts.app>
