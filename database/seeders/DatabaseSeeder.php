<?php

namespace Database\Seeders;

use App\Actions\UploadImage;
use App\Models\LiveEventGallery;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        //
        User::factory()->create([
            'name' => 'User',
            'email' => 'mobistyle35@gmail.com',
            'password' => bcrypt('Asakaboi35!'),
        ]);

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@gallery.com',
            'password' => bcrypt('gallery$!@#4'),
            'role' => 'admin',
        ]);

        Artisan::call('love:reaction-type-add --default');

        $path = public_path('test-images');

        $files = File::files($path);

        for ($index = 0; $index < 40; $index++) {
            $event = LiveEventGallery::factory()->create();
            for ($index = 0; $index < 1; $index++) {
                $fileInfo = collect($files)->random();
                $uploadedFile = new UploadedFile(
                    $fileInfo->getPathname(),
                    $fileInfo->getFilename(),
                    $fileInfo->getType(),
                    null,
                    true
                );

                app(UploadImage::class)->handle($event, $uploadedFile);
            }
        }

        Artisan::call('love:recount');

    }
}
