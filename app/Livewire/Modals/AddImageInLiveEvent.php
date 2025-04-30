<?php

namespace App\Livewire\Modals;

use LivewireUI\Modal\ModalComponent;

class AddImageInLiveEvent extends ModalComponent
{
    public ?int $liveEventId;

    public function render()
    {
        return view('livewire.modals.add-image-in-live-event');
    }

    public static function closeModalOnClickAway(): bool
    {
        return false;
    }

}
