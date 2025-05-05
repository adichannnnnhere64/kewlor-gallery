<?php

namespace App\Models;

use App\Traits\HasNotes;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Plank\Mediable\Mediable;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Category extends Model implements Sortable
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;
    use Mediable;
    use HasNotes;
    use Sluggable;
    use SortableTrait;

    public $sortable = [
        'order_column_name' => 'order_column',
        'sort_when_creating' => true,
    ];

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
