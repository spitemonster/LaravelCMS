<?php

namespace App\Http\Controllers;
use App\Page;
use App\FieldValue;
use App\Template;
use App\Tag;

use Illuminate\Http\Request;
use App\Http\Requests\PageCreationRequest;

class PageController extends Controller
{
    public function showPage($url = '')
    {
        $page = Page::where('url', '/' . $url)->with('children')->first();
        $templateName = Template::where('template_id', $page->template_id)->first()->name;

        if (!$page) {
            abort(404);
        }

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
        $tags = explode(',', $request->tags);

        foreach ($tags as $t) {
            $tag = new Tag;

            $tag->name = trim($t);
            $tag->tag_id = 124433242;
            $tag->page_id = $request->page_id;

            $tag->save();
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
        return "Okay";
    }

    public function getPage(Request $request) {
        if ($request->query('page_id')) {
            $page = Page::where('page_id', $request->query('page_id'))->with('values')->first();
            return $page;
        }

        return Page::where('parent_id', null)->with('children')->get();
    }

    public function updatePage(Request $request) {
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
    }

    public function deletePage(Request $request) {
        if (!$request->query('page_id')) {
            return 'Bad boy';
        }

        $page = Page::where('page_id', $request->query('page_id'))->first();

        $page->delete();

        return 'All good';
    }
}
