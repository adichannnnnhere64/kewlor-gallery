<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Plank\Mediable\Mediable;
use Usamamuneerchaudhary\Commentify\Traits\Commentable;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;
    use Mediable;
    use Commentable;
    use Sluggable;

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
            ],
        ];
    }

    public function getImageAttribute()
    {
        return $this->media?->first()?->getUrl();
    }

    public function getThumbnailAttribute()
    {
        return $this->media?->first()->getUrl('thumbnail');
    }


    /* public function media() */
    /* { */
    /*     return $this->belongsToMany(Media::class); */
    /* } */
}
