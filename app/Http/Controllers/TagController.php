<?php

namespace App\Http\Controllers;
use App\Tag;
use App\PageTag;
use Uuid;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function createTag(Request $request) {
        $test = Tag::where('tag_name', $request->query('tag_name'))->first();
        $res = [];

        if ($test) {
            $res['status'] = 'failure';
            $res['message'] = 'Tag with this name already exists.';

            return $res;
        }

        $tag = new Tag;

        $tag->tag_name = $request->query('tag_name');
        $tag->tag_id = Uuid::generate(4)->string;

        $tag->save();

        $res['status'] = 'success';
        $res['message'] = 'Tag successfully created.';
        $res['tag_name'] = $tag->tag_name;
        $res['tag_id'] = $tag->tag_id;
        $res['tags'] = Tag::get();

        return $res;
    }

    public function getTag(Request $request) {
        // this is the route to get tags
        $data = [];
        $q = $request->query();

        // if user attempts to query by more than one parameter, tell em no
        if (count($q) > 1) {
            return response('You may only query tags with one parameter.', 422);
        }

        // no matter HOW the user requests the tag, we are returning an array of tags
        if ($request->query('tag_id')) {
            // if the user requests by tag id or tag name, they get an array of tags with the associated pages
            $data = Tag::where('tag_id', $request->query('tag_id'))->with('pages')->get();
        } else if ($request->query('tag_name')) {
            $data = Tag::where('tag_name', $request->query('tag_name'))->with('pages')->get();
        } else if ($request->query('page_id')) {
            // if the user requests by a page id, they get an array of tags that are attached to that page
            $pageTag = PageTag::where('page_id', $request->query('page_id'))->with('tag')->get();

            for($i = 0; $i < count($pageTag); ++$i) {
                $data[$i] = $pageTag[$i]->tag->with('pages');
            }
        } else {
            // otherwise, just return all tags with associated pages
            $data = Tag::with('pages')->get();
        }

        if ($request->wantsJson()) {
            return $data;
        }

        return view('tags', ['tags' => $data]);
    }

    public function deleteTag(Request $request) {
        $data = [];
        $tag = Tag::where('tag_id', $request->query('tag_id'))->first();
        $pageTags = PageTag::where('tag_id', $request->query('tag_id'))->get();

        if (!$tag) {
            $data['mode'] = 'failure';
            $data['message'] = 'Invalid Tag ID supplied';

            return $data;
        }

        foreach ($pageTags as $pt) {
            $pt->delete();
        }

        $tag->delete();

        $tags = Tag::get();

        $data['mode'] = 'success';
        $data['message'] = 'Tag successfully deleted';
        $data['tags'] = $tags;

        return $data;
    }
}
