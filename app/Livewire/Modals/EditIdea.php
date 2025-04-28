<?php

namespace App\Livewire\Modals;

use App\Models\LiveEventGallery;
use LivewireUI\Modal\ModalComponent;

class EditIdea extends ModalComponent
{
    public LiveEventGallery $liveEvent;

    public function render()
    {
        return view('livewire.modals.edit-idea');
    }

    public static function closeModalOnClickAway(): bool
    {
        return false;
    }

}
