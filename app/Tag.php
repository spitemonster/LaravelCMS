<?php

namespace App;
use App\PageTag;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $touches = ['page_tag'];

    public function page_tag() {
        return $this->hasMany(PageTag::class, 'tag_id', 'tag_id');
    }

    public function tags() {
        return $this->hasManyThrough(Tag::class, PageTag::class, 'page_id', 'tag_id', 'page_id', 'tag_id');
    }

    public function pages() {
        return $this->hasManyThrough(Page::class, PageTag::class, 'tag_id', 'page_id', 'tag_id', 'page_id');
    }

    public function page_ids() {

    }
}
