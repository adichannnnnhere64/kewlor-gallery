<?php

namespace App\Actions;

use App\Models\LiveEventGallery;
use App\Models\Media;
use App\Models\User;
use Cog\Laravel\Love\Reacter\Facades\Reacter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Plank\Mediable\Facades\MediaUploader;

final class VoteToggle
{
    public function handle(Media $model): void
    {
        DB::transaction(function () use ($model) {
            $user = auth()->user();
            $reactantFacade = $model->viaLoveReactant();
            $isVoted = $reactantFacade->isReactedBy($user, 'Like');
            $reacterFacade = $user->viaLoveReacter();

            if (!$isVoted) {
                $this->like($reacterFacade, $model);

            } else {
                $this->dislike($reacterFacade, $model);
            }
        });

    }

    private function like(Reacter $user, Media $model ): void
    {
            $user->reactTo($model, 'Like');
    }

    private function dislike(Reacter $user, Media $model): void
    {
            $user->unreactTo($model, 'Like');

    }
}
