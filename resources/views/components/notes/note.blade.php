<?php

use function Livewire\Volt\{state, with};
use function Livewire\Volt\{computed};

use App\Models\Note;

state([
    'model' => null,
    'content' => '',
    'editingId' => null,
    'editingContent' => null,
]);

$save = function () {
    $this->validate([
        'content' => 'required',
    ]);

    $this->model->addNote($this->content);
    $this->content = '';
};

$notes = computed(function () {
    return $this->model->notes()->orderBy('created_at', 'desc')->get();
});

$delete = function (Note $model) {
    $model->delete();
};

$edit = function (Note $model) {
    $this->editingId = $model->id;
    $this->editingContent = $model->content;
    $this->dispatch('edit-content');
};

$update = function () {
    Note::find($this->editingId)->update(['content' => $this->editingContent]);
    $this->editingId = null;
};

?>


@volt
    <div>

        <form wire:submit.prevent="save">
            <x-ui.quill rows="6" wire:model="content" toolbar="basic" />

            <div class="flex justify-end">
                <button type="submit"
                    class="my-2 bg-primary-700 hover:bg-primary-800 text-white font-bold py-2 px-4 rounded">Save</button>
            </div>
        </form>

        @foreach ($this->notes as $note)
            <div class="mt-8 mb-10 pb-2">
                <div class="flex justify-between  bg-gray-300/30 p-1 items-center">
                    <h3 class="text-lg opacity-100 font-bold">{{ $note->user->name }}</h3>
                    <p class="text-xs">{{ $note->created_at->diffForHumans() }} |
                        <button @click="$wire.edit({{ $note->id }})"> edit </button>
                        | <button @click="confirm('Are you sure?') && $wire.delete({{ $note->id }})"> delete </button>
                    </p>
                </div>

                @if ($editingId === $note->id)
                    <div class="my-2 edit">
                        <x-ui.quill wire:model="editingContent" toolbar="basic" />
                        <div class="flex justify-end mt-2">
                            <button wire:click="update"
                                class="my-2 bg-primary-700 hover:bg-primary-800 text-white font-bold py-2 px-4 rounded">
                                Update
                            </button>
                            <button wire:click="$set('editingId', null)" class="ml-2 text-gray-600 underline">
                                Cancel
                            </button>
                        </div>
                    </div>
                @else
                    <p>{!! $note->content !!}</p>
                @endif

            </div>
        @endforeach


    </div>
@endvolt
