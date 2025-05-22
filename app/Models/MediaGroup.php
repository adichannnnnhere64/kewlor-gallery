<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class MediaGroup extends Model implements Sortable
{
    /** @use HasFactory<\Database\Factories\MediaGroupFactory> */
    use HasFactory;

    use SortableTrait;

    public $sortable = [
        'order_column_name' => 'order_column',
        'sort_when_creating' => true,
    ];


    public function media()
    {
        return $this->belongsToMany(Media::class)
            ->withPivot('order_column')
            ->withTimestamps();
    }
    /* public function galleries() */
    /* { */
    /*     return $this->belongsToMany(LiveEventGallery::class, 'live_event_gallery_media_group') */
    /*         ->withPivot('order_column') */
    /*         ->orderBy('order_column') */
    /*         ->withTimestamps(); */
    /* } */
}
