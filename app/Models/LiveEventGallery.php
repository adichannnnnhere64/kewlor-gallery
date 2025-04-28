<?php

namespace App\Models;

use App\Traits\HasLikes;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Plank\Mediable\Mediable;
use Usamamuneerchaudhary\Commentify\Traits\Commentable;

class LiveEventGallery extends Model
{
    /** @use HasFactory<\Database\Factories\LiveEventGalleryFactory> */
    use HasFactory;
    use Commentable;

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

    public function getCustomCommentsCountAttribute()
    {
        return $this->comments()->count();
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
