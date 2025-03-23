<?php

use App\Models\LiveEventGallery;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\get;
use function Pest\Laravel\post;


test('live event list shows', function () {

    $models = LiveEventGallery::factory()->times(20)->create();

    $response = get(route('live-event.index'));
    dd($response);


});

test('live event saves', function () {


    Auth::login(User::factory()->create());

    $response = post(route('live-event.create'), [
        'name' => 'test',
        'date' => '2022-01-01'
    ]);

    $response->assertExactJson([
        'message' => "success"
    ]);
});

test('live event validates if no date', function () {


    Auth::login(User::factory()->create());

    $response = post(route('live-event.create'), [
        'name' => 'test',
    ]);

    $response->assertSessionHasErrorsIn('date');
});
