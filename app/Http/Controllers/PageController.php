<?php

namespace App\Http\Controllers;
use App\Page;
use App\FieldValue;
use App\Template;

use Illuminate\Http\Request;
use App\Http\Requests\PageCreationRequest;

class PageController extends Controller
{
    public function getPage($url = '')
    {
        $page = Page::where('url', '/' . $url)->with('children')->first();
        $templateName = Template::where('template_id', $page->template_id)->first()->name;

        if (!$page) {
            abort(404);
        }

        $data = [];

        foreach($page->values as $field) {
            $data[strtolower($field->field_name)] = $field->value;
        }

        return view(strtolower($templateName), $data);
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
}
