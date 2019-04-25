<?php

namespace App\Http\Controllers;
use App\Page;
use App\FieldValue;
use App\Template;
use App\Tag;
use App\User;
use Uuid;
use Auth;

use Illuminate\Http\Request;
use App\Http\Requests\PageCreationRequest;

class PageController extends Controller
{
    public function showPage($url = '')
    {
        $page = Page::where('url', '/' . $url)->with('children')->first();

        if (!$page) {
            return view('404');
        }

        $templateName = Template::where('template_id', $page->template_id)->first()->name;

        $data = [];
        $data['children'] = $page->children()->get();
        $data['tags'] = $page->tags()->get();

        foreach($page->values as $field) {
            $data[strtolower($field->field_name)] = $field->content;
        }

        return view(strtolower(str_replace(' ', '_', $templateName)), $data);
    }

    public function createPage(PageCreationRequest $request)
    {
        $page = new Page;
        $parent = null;

        if ($request->input('parent_id')) {
            $parent = Page::where('page_id', $request->input('parent_id'))->firstOrFail();
        }

        $page->title = $request->input('title');
        $page->template_id = $request->input('template_id');
        $page->parent_id = $request->input('parent_id');
        $page->url = $request->input('url');
        $page->page_id = Uuid::generate(4)->string;
        $page->menu = $request->input('menu');
        $tags = explode(',', $request->input('tags'));

        $page->user_id = Auth::user()->user_id;

        foreach ($tags as $t) {
            if ($t !== '') {
                $tag = new Tag;

                $tag->name = trim($t);
                $tag->tag_id = Uuid::generate(4)->string;
                $tag->page_id = $page->page_id;

                $tag->save();
            }
        }

        // dd($request->input('fields'));

        foreach ($request->input('fields') as $field) {
            $fieldValue = new FieldValue;

            $fieldValue->field_id = $field['field_id'];
            $fieldValue->page_id = $page->page_id;
            $fieldValue->content = $field['content'];
            $fieldValue->field_name = $field['field_name'];
            $fieldValue->type = $field['type'];

            $fieldValue->save();
        }

        $page->save();

        $successMsg = array(
            'status' => 'success',
            'message' => 'Page successfully created'
        );

        return $successMsg;
    }

    public function getPage(Request $request) {
        // if a single page is requested, get it.
        if ($request->query('page_id')) {

            $page = Page::where('page_id', $request->query('page_id'))
                ->with(['values', 'children', 'created_by', 'updated_by'])
                ->first();
            return $page;
        }

        // if not, return all TOP LEVEL pages with their children
        return Page::where('parent_id', null)->with('children')->get();
    }

    public function updatePage(Request $request) {
        if (!$request->query('page_id')) {
            return response('Requested action cannot be completed', 403);
        }

        $page = Page::where('page_id', $request->query('page_id'))->first();

        $page->title = $request->input('title');
        $page->url = $request->input('url');
        $page->menu = $request->input('menu');
        $page->updated_user_id = Auth::user()->user_id;

        foreach ($request->input('fields') as $field) {
            $fieldValue = FieldValue::where('field_id', $field['field_id'])->first();

            $fieldValue->content = $field['content'];

            $fieldValue->save();
        }

        $page->save();

        $successMsg = array(
            'status' => 'success',
            'message' => 'Page successfully updated'
        );

        return $successMsg;
    }

    public function deletePage(Request $request) {
        if (!$request->query('page_id')) {
            return response('Requested action cannot be completed', 403);
        }

        $page = Page::where('page_id', $request->query('page_id'))->first();
        $field_values = FieldValue::where('page_id', $request->query('page_id'))->get();

        foreach ($field_values as $value) {
            $value->delete();
        }

        $page->delete();

        $pages = Page::where('parent_id', null)->with('children')->get();
        // return all remaining pages, specifically for the function in the createPages vue template
        $data = array(
            'status' => 'success',
            'message' => 'Page successfully deleted',
            'pages' => $pages
        );

        return $data;
    }

    public function makeMenu() {
        return Page::where('menu', true)->with(['children' => function ($query) {
            $query->where('menu', true);
        }])->get();
    }
}
