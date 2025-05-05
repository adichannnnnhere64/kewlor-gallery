<?php

namespace App\Models;

use App\Traits\HasLikes;
use App\Traits\HasNotes;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Plank\Mediable\Mediable;

class LiveEventGallery extends Model
{
    /** @use HasFactory<\Database\Factories\LiveEventGalleryFactory> */
    use HasFactory;
    use HasNotes;

    use Mediable;
    use Sluggable;
    use HasLikes;

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
            ],
        ];
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function getGalleryAttribute()
    {
        return $this->getMedia('default');
    }
}
