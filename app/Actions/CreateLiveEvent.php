<?php

namespace App\Actions;

use App\Models\LiveEventGallery;

final class CreateLiveEvent
{
    public function handle( array $data): LiveEventGallery
    {
        return LiveEventGallery::create($data);
    }
}
