<?php

namespace App\Models;

use App\Traits\HasLikes;
use App\Traits\HasNotes;
use Cog\Contracts\Love\Reactable\Models\Reactable as ReactableInterface;
use Cog\Laravel\Love\Reactable\Models\Traits\Reactable;
use Plank\Mediable\Media as BaseMedia;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;


/**
 * @method static UserEloquentBuilder query()
 */
class Media extends BaseMedia implements Sortable
{
    use HasLikes;
    use SortableTrait;
    use HasNotes;

    public $sortable = [
        'order_column_name' => 'order_column',
        'sort_when_creating' => true,
    ];

    public function getVideoThumbnailAttribute()
    {
        if ($this->mime_type === 'video/mp4') {
            return $this->video_thumbnail_id ? Media::find($this->video_thumbnail_id)?->getUrl() : '/placeholder.jpg';
        }

        if (\Str::startsWith($this->mime_type, 'audio/')) {
            return '/audio.jpg';
        }

        return '/placeholder.jpg';
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
}
