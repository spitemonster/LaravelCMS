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

    public function page() {
        return $this->hasMany(Page::class, 'page_id', 'page_id');
    }
}
