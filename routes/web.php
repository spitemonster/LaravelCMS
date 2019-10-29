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
use App\PageTag;
use App\Tag;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestEmail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
// use Storage;
// use App\Http\Middleware\CheckApiToken;
// use \Uuid;

Route::get('/media{filename?}', 'MediaController@getMedia');
Route::post('/media', 'MediaController@createMedia');
Route::delete('/media', 'MediaController@deleteMedia');

Route::get('/mail', function() {
    Mail::to('johnstk91@gmail.com')->send(new TestEmail());

    return 'Yep';
});

Route::get('/tag', function(Request $request) {

    // if someone visits /tag either show them the pages related to that tag if given a name or id
    // or just display a list of tags
    $data = [];

    if ($request->query('tag_id')) {
        $data['pages'] = PageTag::where('tag_id', $request->query('tag_id'))->first()->pages;
    } elseif ($request->query('tag_name')) {
        $data['pages'] = Tag::where('name', $request->query('tag_name'))->first()->pages;
    } else {
        $data['tags'] = Tag::get();
    }

    return view('tags', $data);
});

Route::get('/mailable', function() {
    return new TestEmail();
});

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

// Route::post('/media', function(Request $request) {
//     $file = $request->file('file')->store('media', 'public');

//     $data = array(
//         'status' => 'success',
//         'message' => 'File successfully uploaded',
//         'url' => '/' . $file
//     );

//     $media = new Media;

//     $media->url = $file;
//     $media->filename = 'poop';
//     $media->page_id = $data['pageId'] = Uuid::generate(4)->string;

//     $media->save();

//     return $data;
// });

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