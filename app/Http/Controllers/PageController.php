<?php

namespace App\Http\Controllers;
use App\Page;
use App\FieldValue;
use App\Template;
use App\Tag;
use App\User;
use App\PageTag;
use Uuid;
use Auth;

use Illuminate\Http\Request;
use App\Http\Requests\PageCreationRequest;

class PageController extends Controller
{
    public function showPage($url = '')
    {
        $page = Page::where('url', '/' . $url)->with('children')->first();

        if ($page->private && !Auth::user()) {
            // temporary functionality for private pages
            // if the page is private and the user is not logged in, show them the 404 page
            return view('404');
        }

        // gather all menu pages and return them with every page request
        $menu = Page::where([['menu', true], ['private', false]])->get(['title', 'url']);

        // if the page doesn't exist, send that sweet sweet 404
        if (!$page) {
            return view('404');
        }

        // find the template name so we know what to render with
        $templateName = Template::where('template_id', $page->template_id)->first()->name;
        $tags = $page->tags()->get();

        // $data represents the fields and values. splitting them into the $data array allows them to be accessed by just the field name on the front end
        $data = [];

        $data['menu'] = $menu;
        $data['children'] = $page->children()->get();
        $data['tags'] = [];

        foreach ($tags as $tag) {
            array_push($data['tags'], $tag);
        }

        $data['description'] = $page->description;

        foreach($page->values as $field) {
            $data[strtolower($field->field_name)] = $field->content;
        }

        $page->views = $page->views + 1;

        $page->save();

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
        $page->private = $request->input('private');
        $page->description = $request->input('description');
        $tags = explode(',', $request->input('tags'));

        $page->user_id = $page->updated_user_id = Auth::user()->user_id;

        foreach ($tags as $t) {
            // test if tag already exists
            $test = Tag::where('name', $t)->first();
            // if so, don't create the tag, just make the association.
            if ($test) {
                $pageTag = new PageTag;
                $pageTag->tag_id = $test->tag_id;
                $pageTag->page_id = $page->page_id;

                $pageTag->save();
            } else {
                $tag = new Tag;
                $pageTag = new PageTag;

                $tag->name = trim($t);

                $tag->tag_id = $pageTag->tag_id = Uuid::generate(4)->string;

                $pageTag->page_id = $page->page_id;

                $pageTag->save();
                $tag->save();
            }
        }

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
                ->with(['values', 'children', 'created_by', 'updated_by', 'tags'])
                ->first();

            return $page;
        }

        // if not, return all TOP LEVEL pages with their children
        return Page::where('parent_id', null)->with(['values', 'children', 'created_by', 'updated_by'])->get();
    }

    public function updatePage(Request $request) {
        if (!$request->query('page_id')) {
            return response('Requested action cannot be completed', 403);
        }

        $page = Page::where('page_id', $request->query('page_id'))->get()->first();

        $page->title = $request->input('title');
        $page->url = $request->input('url');
        $page->menu = $request->input('menu');
        $page->private = $request->input('private');
        $page->description = $request->input('description');
        $page->updated_user_id = Auth::user()->user_id;
        $tags = explode(',', $request->input('tags'));

        foreach ($tags as $tg) {
            $t = trim($tg);
            // test if tag already exists
            $testTag = Tag::where('name', $t)->first();

            // if so, don't create the tag, just make the association.
            if ($testTag) {
                $testPageTag = PageTag::where([['page_id', $page->page_id], ['tag_id', $testTag->tag_id]])->first();

                if (!$testPageTag) {
                    $pageTag = new PageTag;

                    $pageTag->page_id = $page->page_id;
                    $pageTag->tag_id = $testTag->tag_id;

                    $pageTag->save();
                }

            } else {
                $tag = new Tag;
                $pageTag = new PageTag;

                $tag->name = $t;

                $tag->tag_id = $pageTag->tag_id = Uuid::generate(4)->string;

                $pageTag->page_id = $page->page_id;

                $pageTag->save();
                $tag->save();
            }
        }

        foreach ($request->input('fields') as $field) {
            $fieldValue = $page->values()->where('field_id', $field['field_id'])->first();

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
        return Page::where([['menu', true], ['private', false]])->with(['children' => function ($query) {
            $query->where([['menu', true], ['private', false]]);
        }])->get();
    }
}
