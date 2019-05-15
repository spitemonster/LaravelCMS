<?php

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

use App\User;
use App\Page;
use App\Template;
use App\Field;
use App\FieldValue;
use App\Media;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
// use Storage;
// use App\Http\Middleware\CheckApiToken;
// use Uuid;

Route::get('/logout', function(Request $request) {
    Auth::logout();

    return redirect('/');
});

Route::get('/user', function(Request $request) {
    if (!$request->query('user_id')) {
        $authUser = Auth::user();

        if (!$authUser) {
            return 'You must be logged in or have a valid User ID';
        }

        return $authUser;
    }

    $user = User::where('user_id', $request->query('user_id'))->first();

    return $user;
});

Route::get('/users', function(Request $request) {
    return User::all();
});

Route::delete('/user', function(Request $request) {
    $requestingUser = User::where('api_token', $request->query('api_token'))->first();
    $targetUser = User::where('user_id', $request->query('user_id'))->first();

    // check if user is NOT a superuser
    if (!$requestingUser->superuser) {

        // if they're NOT a superuser, they still are allowed to delete their own account
        if ($requestingUser == $targetUser) {
            $targetUser->delete();

            $allowedData = array(
                'status' => 'success',
                'message' => 'User successfully deleted',
                'users' => User::all()
            );

            return $allowedData;
        }

        // if they attempt to delete an account that is not their own, they can go straight to heck
        $disallowedData = array(
            'status' => 'error',
            'message' => 'You do not have the required permission to complete that action',
        );

        return $disallowedData;
    }

    // if they ARE a superuser they can do whatever they darn well please
    $targetUser->delete();

    $allowedData = array(
        'status' => 'success',
        'message' => 'User successfully deleted',
        'users' => User::all()
    );

    return $allowedData;
});

Route::post('/media', function(Request $request) {
    $file = $request->file('file')->store('media', 'public');

    $data = array(
        'status' => 'success',
        'message' => 'File successfully uploaded',
        'url' => '/' . $file
    );

    $media = new Media;

    $media->url = $file;
    $media->filename = 'poop';
    $media->page_id = '124444';

    $media->save();

    return $data;
});

// CRUD template
Route::middleware('auth:api')->post('/template', 'TemplateController@createTemplate');
Route::get('/template', 'TemplateController@getTemplate');
Route::middleware('auth:api')->patch('/template', 'TemplateController@updateTemplate');
Route::middleware('auth:api')->delete('/template', 'TemplateController@deleteTemplate');

// CRUD page
Route::middleware('auth:api')->post('/page', 'PageController@createPage');
Route::get('/page', 'PageController@getPage');
Route::middleware('auth:api')->patch('/page', 'PageController@updatePage');
Route::middleware('auth:api')->delete('/page', 'PageController@deletePage');

// Given a slug, tests to see if it's still available. If not, iterates until it finds one that is available.
Route::get('/url', function(Request $request) {
    $slug = $request->query('slug');

    function testSlug($slug, $it = 1) {
        $test = ($it > 1) ? $slug . '-' . $it : $slug;

        $page = Page::where('url', $test)->first();

        if (!$page) {
            return $test;
        }

        return testSlug($slug, ($it + 1));
    }

    return testSlug($slug);
});

Auth::routes();

Route::get('/admin', 'DashboardController@index')->name('admin');

Route::get('/admin/{any}', 'DashboardController@redirect')->where('any', '.*');

Route::get('/{url}', 'PageController@showPage')->where('url', '.*');