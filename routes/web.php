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
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

// CRUD template
Route::post('/template', 'TemplateController@createTemplate');
Route::get('/template', 'TemplateController@getTemplate');
Route::patch('/template', 'TemplateController@updateTemplate');
Route::delete('/template', 'TemplateController@deleteTemplate');

// CRUD page
Route::post('/page', 'PageController@createPage');
Route::get('/page', 'PageController@getPage');
Route::patch('/page', 'PageController@updatePage');
Route::delete('/page', 'PageController@deletePage');

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