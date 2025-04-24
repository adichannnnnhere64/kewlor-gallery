<?php

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LiveEventGalleryController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/deploy', function () {

    function callCloudwaysAPI($method, $url, $accessToken, $post = [])
    {
        $API_URL = 'https://api.cloudways.com/api/v1';

        $baseURL = $API_URL;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_URL, $baseURL.$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($accessToken) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer '.$accessToken]);
        }

        $encoded = '';
        if (count($post)) {
            foreach ($post as $name => $value) {
                $encoded .= urlencode($name).'='.urlencode($value).'&';
            }
            $encoded = substr($encoded, 0, strlen($encoded) - 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded);
            curl_setopt($ch, CURLOPT_POST, 1);
        }
        $output = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpcode != '200') {
            exit('An error occurred code: '.$httpcode.' output: '.substr($output, 0, 10000));
        }
        curl_close($ch);

        return json_decode($output);
    }

    $API_KEY = 'YmVVZpe9OKlmPTlMhVcPGcIijjBjjA';
    $EMAIL = 'olaf@kewlor.com';

    // Fetch Access Token
    $tokenResponse = callCloudwaysAPI('POST', '/oauth/access_token', null, [
        'email' => $EMAIL,
        'api_key' => $API_KEY,
    ]);
    $accessToken = $tokenResponse->access_token;
    $gitPullResponse = callCloudwaysAPI('POST', '/git/pull', $accessToken, [
        'server_id' => $_GET['server_id'],
        'app_id' => $_GET['app_id'],
        'git_url' => $_GET['git_url'],
        'branch_name' => $_GET['branch_name'],
        /* Uncomment it if you want to use deploy path, Also add the new parameter in your link
        'deploy_path' => $_GET['deploy_path']
        */
    ]);
    echo json_encode($gitPullResponse);

});

Route::redirect('home', '/')->name('home');

Route::redirect('login', 'auth/login');

Route::middleware('auth')->group(function (): void {
    Route::get('email/verify/{id}/{hash}', EmailVerificationController::class)
        ->middleware('signed')
        ->name('verification.verify');
    Route::post('logout', LogoutController::class)
        ->name('logout');

    Route::post('upload/{model}', [UploadController::class, 'store'])->name('upload');
    /* Route::post('live-event', [LiveEventGalleryController::class, 'store'])->name('live-event.create'); */
    Route::get('live-event', [LiveEventGalleryController::class, 'index'])->name('live-event.index')->middleware(['can:access-admin-panel']);
    Route::post('/live-event/{model}', [LiveEventGalleryController::class, 'delete'])->name('live-event.delete')->middleware(['can:access-admin-panel']);



    /* Route::get('live-event/{model}', [LiveEventGalleryController::class, 'edit'])->name('live-event.edit'); */


    Route::get('category', [CategoryController::class, 'index'])->name('category.index')->middleware(['can:access-admin-panel']);
    Route::post('/category/{model}', [CategoryController::class, 'delete'])->name('category.delete')->middleware(['can:access-admin-panel']);
});
