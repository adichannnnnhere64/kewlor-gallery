<?php

namespace App\Livewire\Modals;

use LivewireUI\Modal\ModalComponent;

class CreateIdea extends ModalComponent
{
    public ?int $categoryId;

    public function render()
    {
        return view('livewire.modals.create-idea');
    }

    public static function closeModalOnClickAway(): bool
    {
        return false;
    }

}
