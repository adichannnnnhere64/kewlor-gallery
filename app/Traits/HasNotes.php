<?php

namespace App\Traits;

use App\Models\Note;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasNotes
{
    public function notes()
    {
        return $this->morphMany(Note::class, 'notable');
    }

    public function addNote(string $content)
    {
        return $this->notes()->create([
            'content' => $content,
            'user_id' => auth()->id(),
        ]);
    }
}
