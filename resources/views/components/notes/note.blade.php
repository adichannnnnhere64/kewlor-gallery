<?php

use function Livewire\Volt\{state, with};
use function Livewire\Volt\{computed};

use App\Models\Note;

state([
    'model' => null,
    'content' => '',
]);

$save = function () {
    $this->validate([
        'content' => 'required',
    ]);

    $this->model->addNote($this->content);
    $this->content = "";
};

$notes = computed(function () {
    return $this->model->notes()->orderBy('created_at', 'desc')->get();
});

$delete = function (Note $model) {
    $model->delete();
};

?>


@volt
    <div>

        <form wire:submit.prevent="save">
        <x-ui.quill wire:model="content" toolbar="basic"/>

        <div class="flex justify-end">
            <button type="submit" class="my-2 bg-primary-700 hover:bg-primary-800 text-white font-bold py-2 px-4 rounded"
                >Save</button>
        </div>
        </form>


        @foreach ($this->notes as $note)
            <div class="my-4">
                <div class="flex justify-between">
                    <h3 class="text-lg font-bold">{{ $note->user->name }}</h3>
                    <p class="text-xs">{{ $note->created_at->diffForHumans() }} | <button @click="confirm('Are you sure?') && $wire.delete({{ $note->id }})" > delete </button></p>
                </div>
                <p>{!! $note->content !!}</p>
            </div>

        @endforeach


    </div>
@endvolt
