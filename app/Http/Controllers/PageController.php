<?php

namespace App\Http\Controllers;
use App\Page;
use Illuminate\Http\Request;
use App\Http\Requests\PageCreationRequest;

class PageController extends Controller
{
    public function getPage($url = '')
    {
        $page = Page::where('url', '/' . $url)->with('children')->first();

        if (!$page) {
            abort(404);
        }

        $data = [
            'content' => $page->content,
            'title' => $page->title,
        ];

        return view('page', $data);
    }

    public function createPage(PageCreationRequest $request)
    {
        $page = new Page;

        $page->title = $request->title;
        $page->content = $request->content;
        $page->parent_id = $request->parent_id;
        $page->url = $request->parent_id ? Page::where('id', $request->parent_id)->first()->url . $request->url : $request->url;

        $page->save();
        return redirect($page->url);
    }
}
