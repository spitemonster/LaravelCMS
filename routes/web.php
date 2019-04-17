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

Route::get('/page', function(Request $request) {
    if ($request->query('page_id')) {
        $page = Page::where('page_id', $request->query('page_id'))->with('values')->first();
        return $page;
    }

    return Page::all();
})->where('test', '.*');

Route::patch('/page', function(Request $request) {
    if ($request->query('page_id')) {
        $page = Page::where('page_id', $request->query('page_id'))->first();
        $values = $page->values;

        $page->title = $request->title;
        $page->url = $request->url;

        foreach ($request->fields as $field) {
            $fieldValue = FieldValue::where('field_id', $field['field_id'])->first();

            $fieldValue->value = $field['value'];

            $fieldValue->save();
        }

        $page->save();
        return "Okay";
    }
});

Route::patch('/template', function(Request $request) {
    if (!$request->query('template_id')) {
        return 'Bad boy';
    }

    $template = Template::where('template_id', $request->query('template_id'))->first();
    $template->name = $request->name;

    foreach (json_decode($request->fields) as $field) {
        // dd($field->name);
        $f = Field::where('field_id', $field->field_id)->first();

        if(!$f) {
            $f = new Field;
            $f->type = $field->type;
            $f->field_id = $field->field_id;
            $f->template_id = $request->query('template_id');
        }

        $f->name = $field->name;
        $f->required = true;
        $f->save();
    }

    $template->save();

    return "Okay";
});

Route::delete('/field', function(Request $request) {
    if (!$request->query('field_id')) {
        return 'Bad Boy';
    }

    $field = Field::where('field_id', $request->query('field_id'))->first();
    $fieldValue = FieldValue::where('field_id', $request->query('field_id'))->all();

    foreach ($fieldValue as $value) {
        $value->delete();
    }

    $field->delete();

    return 'All good';
});

Route::delete('/template', function(Request $request) {
    if (!$request->query('template_id')) {
        return 'Bad boy';
    }

    $template = Template::where('template_id', $request->query('template_id'))->first();
    $fields = $template->fields()->get();

    foreach ($fields as $field) {
        $field->delete();
    }

    $template->delete();

    return 'All good';
});

Route::delete('/page', function(Request $request) {
    if (!$request->query('page_id')) {
        return 'Bad boy';
    }

    $page = Page::where('page_id', $request->query('page_id'))->first();

    $page->delete();

    return 'All good';
});

Route::post('/page', 'PageController@createPage');

Route::post('/template', 'TemplateController@createTemplate');

Route::get('/template', function(Request $request) {

    if ($request->query('template_id')) {
        $template_id = $request->query('template_id');

        $template = [];

        $template[] = Template::where('template_id', $template_id)->first();
        $template[0]['fields'] = Template::where('template_id', $template_id)->first()->fields;

        return json_encode($template[0]);
    }

    return json_encode(Template::all());
});

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

Route::get('/{url}', 'PageController@getPage')->where('url', '.*');
