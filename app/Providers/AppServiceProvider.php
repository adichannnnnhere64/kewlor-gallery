<?php

namespace App\Providers;

use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Intervention\Image\Image;
use Intervention\Image\Typography\FontFactory;
use Opcodes\LogViewer\Facades\LogViewer;
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

        LogViewer::auth(function ($request) {
            return $request->user() && in_array($request->user()->email, ['mobistyle35@gmail.com', 'admin@gallery.com']);
        });

        Model::unguard();

        LogViewer::auth(function ($request) {
            return $request->user() && in_array($request->user()->email, ['mobistyle35@gmail.com']);
        });

        Gate::define('access-admin-panel', function (User $user) {
            return $user->role === 'admin';
        });

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

                $text = setting('watermark') ?? config('app.name');

                $watermarkColor = 'rgba(255, 255, 255, 0.5)';

                $fontSize = min(
                    100,
                    max(
                        20,
                        $image->width() * 0.03
                    )
                );

                // Calculate padding (2% of image width)
                $paddingX = $image->width() * (float) setting('xaxis') ?? 0.02;
                $paddingY = $image->width() * (float) setting('yaxis') ?? 0.02;

                // Position at bottom right
                $x = $image->width() - $paddingX;
                $y = $image->height() - $paddingY;

                $image->text($text, $x, $y, function (FontFactory $font) use ($fontSize, $watermarkColor) {
                    $font->size($fontSize);
                    $font->color($watermarkColor);
                    $font->file(public_path('roboto.ttf'));
                    $font->align('right');
                    $font->valign('bottom');
                    $font->angle(0);
                });

            })->setOutputQuality(80)->outputWebpFormat());

        ImageManipulator::defineVariant(
            'preview',
            ImageManipulation::make(function (Image $image, Media $originalMedia) {
                $text = setting('watermark') ?? config('app.name');

                $watermarkColor = 'rgba(255, 255, 255, 0.5)';

                $fontSize = min(
                    100,
                    max(
                        20,
                        $image->width() * 0.03
                    )
                );

                $paddingX = $image->width() * (float) setting('xaxis') ?? 0.02;
                $paddingY = $image->width() * (float) setting('yaxis') ?? 0.02;

                $x = $image->width() - $paddingX;
                $y = $image->height() - $paddingY;

                $image->text($text, $x, $y, function (FontFactory $font) use ($fontSize, $watermarkColor) {
                    $font->size($fontSize);
                    $font->color($watermarkColor);
                    $font->file(public_path('roboto.ttf'));
                    $font->align('right');
                    $font->valign('bottom');
                    $font->angle(0);
                });

            })->outputWebpFormat());

    }
}
