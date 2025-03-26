<?php

namespace App\Models;

use Cog\Contracts\Love\Reactable\Models\Reactable as ReactableInterface;
use Cog\Laravel\Love\Reactable\Models\Traits\Reactable;
use Plank\Mediable\Media as BaseMedia;
use Usamamuneerchaudhary\Commentify\Traits\Commentable;

class Media extends BaseMedia implements ReactableInterface
{
    use Commentable, Reactable;
}
