<?php

namespace App\Models;

use Cog\Contracts\Love\Reactable\Models\Reactable as ReactableInterface;
use Cog\Laravel\Love\Reactable\Models\Traits\Reactable;
use Plank\Mediable\Media as BaseMedia;
use Usamamuneerchaudhary\Commentify\Traits\Commentable;

/**
 * @method static UserEloquentBuilder query()
 */
class Media extends BaseMedia implements ReactableInterface
{
    use Commentable, Reactable;

    public function getCommentsCountAttribute()
    {
        return $this->comments()->count();
    }

    public function newEloquentBuilder($query): MediaEloquentBuilder
    {
        return new MediaEloquentBuilder($query);
    }
}
