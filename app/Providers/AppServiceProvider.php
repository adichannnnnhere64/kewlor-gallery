<?php

namespace App\Providers;

use Opcodes\LogViewer\Facades\LogViewer;
use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Intervention\Image\Image;
use Plank\Mediable\Facades\ImageManipulator;
use Plank\Mediable\ImageManipulation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();

       /* LogViewer::auth(function ($request) {        return $request->user()            && in_array($request->user()->email, [                'mobistyle35@gmail.com',            ]);    }); */

        Gate::define('access-admin-panel', function (User $user) {
            return $user->role === 'admin';
        });

        /*                 ImageManipulator::defineVariant('default', */
        /*                 ImageManipulation::make(function($image) { */
        /*                     $image->sharpen(5); */
        /*                 })->setOutputQuality(75)->outputWebpFormat() */
        /*             ); */

        ImageManipulator::defineVariant(
            'thumbnail',
            ImageManipulation::make(function (Image $image, Media $originalMedia) {
                $originalWidth = $image->width();
                $originalHeight = $image->height();

                $largeImageThreshold = 600;
                $maxThumbnailSize = 400;

                if ($originalWidth > $largeImageThreshold || $originalHeight > $largeImageThreshold) {
                    $ratio = min(
                        $maxThumbnailSize / $originalWidth,
                        $maxThumbnailSize / $originalHeight
                    );
                    $image->resize(
                        (int) ($originalWidth * $ratio),
                        (int) ($originalHeight * $ratio),
                        function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        }
                    );
                    $image->sharpen(5);
                }
            })->setOutputQuality(80)->outputWebpFormat());

    }
}
