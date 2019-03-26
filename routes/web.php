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
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

// Route::get('/', function () {

//     $name = Auth::user() ? Auth::user()->name : User::find(1)->name;
//     $data = [
//         'name' => $name
//     ];

//     return view('welcome', $data);
// });

Route::get('/goodbye', function() {
    return view('goodbye');
});

Route::post('/page', 'PageController@createPage');

Auth::routes();

Route::get('/admin', 'DashboardController@index')->name('admin');

Route::get('/create', function() {
    $data = [
        'pages' => Page::all()
    ];

    return view('create', $data);
});

Route::any('/{url}', 'PageController@getPage')->where('url', '.*');