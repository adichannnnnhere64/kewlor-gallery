<?php

namespace App\Models;

use App\Traits\HasLikes;
use App\Traits\HasNotes;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Plank\Mediable\Mediable;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;


class LiveEventGallery extends Model implements Sortable
{
    /** @use HasFactory<\Database\Factories\LiveEventGalleryFactory> */
    use HasFactory;
    use HasNotes;

    use Mediable;
    use Sluggable;
    use HasLikes;
    use SortableTrait;

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
            ],
        ];
    }

    public $sortable = [
        'order_column_name' => 'order_column',
        'sort_when_creating' => true,
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function getGalleryAttribute()
    {
        return $this->getMedia('default');
    }
}
