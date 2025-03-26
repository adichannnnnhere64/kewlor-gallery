<?php

namespace App\Models;

use Plank\Mediable\Media as BaseMedia;
use Usamamuneerchaudhary\Commentify\Traits\Commentable;
use Cog\Contracts\Love\Reactable\Models\Reactable as ReactableInterface;
use Cog\Laravel\Love\Reactable\Models\Traits\Reactable;

class Media extends BaseMedia implements ReactableInterface
{
    use Commentable, Reactable;
}
