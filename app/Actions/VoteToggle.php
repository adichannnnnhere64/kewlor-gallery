<?php

namespace App\Actions;

use App\Models\Media;
use Cog\Laravel\Love\Reacter\Facades\Reacter;
use Illuminate\Support\Facades\DB;

final class VoteToggle
{
    public function handle(Media $model, string $type): void
    {
        DB::transaction(function () use ($model, $type) {

            $user = auth()->user();
            $reacterFacade = $user->viaLoveReacter();

            if ($type == 'like') {
                $this->like($reacterFacade, $model);
            }


            if ($type == 'dislike') {
                $this->dislike($reacterFacade, $model);
            }

        });

    }

    private function like(Reacter $user, Media $model): void
    {
       $reactantFacade = $model->viaLoveReactant();
       $isLiked = $reactantFacade->isReactedBy(auth()->user(), 'Like');

        if ($isLiked) {
            $user->unreactTo($model, 'Like');
            return;
        }

        $user->reactTo($model, 'Like');
    }

    private function dislike(Reacter $user, Media $model): void
    {
       $reactantFacade = $model->viaLoveReactant();
       $isDisliked = $reactantFacade->isReactedBy(auth()->user(), 'Dislike');

        if ($isDisliked) {
            $user->unreactTo($model, 'Dislike');
            return;
        }

        $user->reactTo($model, 'Dislike');

    }
}
