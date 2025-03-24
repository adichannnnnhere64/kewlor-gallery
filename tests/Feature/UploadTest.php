<?php

use App\Models\LiveEventGallery;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\assertModelExists;
use function Pest\Laravel\post;

test('can upload image', function (): void {

    Storage::fake();

    $image = UploadedFile::fake()->image('image.jpg');
    Auth::login(User::factory()->create());

    $model = LiveEventGallery::factory()->create();

    $response = post(route('upload', [$model->id]), [
        'file' => $image,
    ]);

    $response->assertStatus(200);
    $response->assertExactJson([
        'message' => 'success',
    ]);

    $model->refresh();
    assertModelExists($model->getMedia('default')->first());
});
