<?php

namespace App;
use App\Tag;
use Illuminate\Database\Eloquent\Model;

class PageTag extends Model
{
    protected $touches = ['page'];

    public function pages() {
        return $this->hasMany(Page::class, 'page_id', 'page_id');
    }

    public function page() {
        return $this->hasMany(Page::class, 'page_id', 'page_id');
    }

    public function tag() {
        return $this->belongsTo(Tag::class, 'tag_id', 'tag_id');
    }
}
