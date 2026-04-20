<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\PagesController;
use App\Http\Controllers\Frontend\ArticlesController;
use App\Http\Controllers\Frontend\EventsController;
use App\Http\Controllers\Frontend\ImageCollectionsController;
use App\Http\Controllers\Frontend\PeopleController;
use App\Http\Controllers\Frontend\ResourceAuthenticationController;
use App\Http\Controllers\TinyMceUploadController;


Route::get('/autologin', function (Request $request) {
    $expires  = $request->input('expires');
    $username = $request->input('username');
    $signature = $request->input('signature');

    // Check expiry
    abort_if(now()->timestamp > $expires, 403);

    // Rebuild and verify signature
    $payload = implode('|', [
        config('app.url'),
        $request->username,
        $request->expires,
    ]);

    $expected = hash_hmac('sha256', $payload, env('SEED_API_KEY'));
    abort_unless(hash_equals($expected, $request->signature), 403);

    // Look up and guard the user
    $user = \App\Models\User::where('username', $request->username)->firstOrFail();
    abort_unless($user->is_admin, 403);

    Auth::login($user);

    return redirect()->to(filament()->getHomeUrl());
});

Route::middleware('web')->group(function () {

    Route::post('authenticate-resource', [ResourceAuthenticationController::class, 'check']);

    Route::get('/news', [ArticlesController::class, 'index']);
    Route::get('/news/{article:slug}', [ArticlesController::class, 'show']);

    Route::get('/events/{event:slug}', [EventsController::class, 'show']);
    Route::get('/events', [EventsController::class, 'index']);
    Route::get('/calendar/{month?}/{year?}', [EventsController::class, 'calendar']);

    Route::get('/photos', [ImageCollectionsController::class, 'index']);
    Route::get('/photos/{image_collection:slug}', [ImageCollectionsController::class, 'show']);

    Route::get('/executive-committee', [PeopleController::class, 'executiveCommittee']);
    Route::get('/current-members', [PeopleController::class, 'currentMembers']);
    Route::get('/prospective-members', [PeopleController::class, 'prospectiveMembers']);

    Route::get('/', [HomeController::class, 'show']);

    /**
     * Catch-all page route MUST be last.
     * Use slug binding so /about maps to Page where slug=about.
     */
    Route::get('/{page:slug}', [PagesController::class, 'show'])
        ->where('page', '.*');
});

Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/tinymce/upload', [TinyMceUploadController::class, 'upload'])->name('tinymce.upload');
});