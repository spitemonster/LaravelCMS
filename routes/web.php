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
// use Uuid;
// use Storage;
// use App\Http\Middleware\CheckApiToken;
// use \Uuid;

Route::get('/media{filename?}', 'MediaController@getMedia');
Route::post('/media', 'MediaController@createMedia');
Route::delete('/media', 'MediaController@deleteMedia');

// Tag Functions
Route::post('/tag', 'TagController@createTag');
Route::get('/tag', 'TagController@getTag');
Route::delete('/tag', 'TagController@deleteTag');

Route::get('/logout', function(Request $request) {
    Auth::logout();

    return redirect('/');
});

Route::middleware('auth:api')->patch('/user', 'UserController@updateUser');
Route::middleware('auth:api')->delete('/user', 'UserController@deleteUser');
// as with tags, /user and /users as functions can be combined
Route::get('/user', 'UserController@getUser');
Route::middleware('auth:api')->get('/users', function(Request $request) {
    return User::all();
});


Route::middleware('auth:api')->post('/token', 'UserController@newApiToken');

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

Route::middleware('auth:api')->delete('/field', 'FieldController@deleteField');

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