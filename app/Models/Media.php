<?php

namespace App\Models;

use App\Traits\HasLikes;
use Cog\Contracts\Love\Reactable\Models\Reactable as ReactableInterface;
use Cog\Laravel\Love\Reactable\Models\Traits\Reactable;
use Plank\Mediable\Media as BaseMedia;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

use Usamamuneerchaudhary\Commentify\Traits\Commentable;

/**
 * @method static UserEloquentBuilder query()
 */
class Media extends BaseMedia implements Sortable
{
    use Commentable;
    use HasLikes;
    use SortableTrait;

    public $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
    ];

    public function getCustomCommentsCountAttribute()
    {
        return $this->comments()->count();
    }

    public function getVideoThumbnailAttribute()
    {
        return $this->video_thumbnail_id ? Media::find($this->video_thumbnail_id)?->getUrl() : null;
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
}
