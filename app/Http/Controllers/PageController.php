<?php

namespace App\Http\Controllers;
use App\Page;
use App\FieldValue;
use App\Template;
use App\Tag;
use Uuid;

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
            $data[strtolower($field->field_name)] = $field->value;
        }

        return view(strtolower(str_replace(' ', '_', $templateName)), $data);
    }

    public function createPage(PageCreationRequest $request)
    {
        $page = new Page;
        $parent = null;

        if ($request->parent_id) {
            $parent = Page::where('page_id', $request->parent_id)->firstOrFail();
        }

        $page->title = $request->title;
        $page->template_id = $request->template_id;
        $page->parent_id = $request->parent_id;
        $page->url = $request->url;
        $page->page_id = $request->page_id;
        $page->menu = $request->menu;
        $tags = explode(',', $request->tags);

        foreach ($tags as $t) {
            if ($t !== '') {
                $tag = new Tag;

                $tag->name = trim($t);
                $tag->tag_id = Uuid::generate(4)->string;
                $tag->page_id = $request->page_id;

                $tag->save();
            }
        }

        foreach ($request->fields as $field) {
            $fieldValue = new FieldValue;

            $fieldValue->field_id = $field['field_id'];
            $fieldValue->page_id = $request['page_id'];
            $fieldValue->value = $field['content'];
            $fieldValue->field_name = $field['field_name'];
            $fieldValue->type = $field['type'];

            $fieldValue->save();
        }

        $page->save();
        return response('Page successfully created', 200);
    }

    public function getPage(Request $request) {
        // if a single page is requested, get it.
        if ($request->query('page_id')) {
            $page = Page::where('page_id', $request->query('page_id'))->with('values')->with('children')->first();
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
        $values = $page->values;

        $page->title = $request->title;
        $page->url = $request->url;

        foreach ($request->fields as $field) {
            $fieldValue = FieldValue::where('field_id', $field['field_id'])->first();

            $fieldValue->value = $field['value'];

            $fieldValue->save();
        }

        $page->save();
        return response('Page Updated', 200);
    }

    public function deletePage(Request $request) {
        if (!$request->query('page_id')) {
            return response('Requested action cannot be completed', 403);
        }

        $page = Page::where('page_id', $request->query('page_id'))->first();

        $page->delete();

        // return all remaining pages, specifically for the function in the createPages vue template
        return Page::where('parent_id', null)->with('children')->get();
    }
}
