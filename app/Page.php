<?php

namespace App;
use App\Traits\Taggable;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use Taggable;

    public function parent()
    {
        return $this->belongsTo(Page::class, 'parent_id', 'page_id');
    }

    public function children()
    {
        return $this->hasMany(Page::class, 'parent_id', 'page_id')->with('updated_by');
    }

    public function values()
    {
        return $this->hasMany(FieldValue::class, 'page_id', 'page_id');
    }

    public function created_by()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id')->select(['user_id', 'name']);
    }

    public function updated_by()
    {
        return $this->belongsTo(User::class, 'updated_user_id', 'user_id')->select(['user_id', 'name']);;
    }

    public function tags() {
        return $this->hasManyThrough(Tag::class, PageTag::class, 'page_id', 'tag_id', 'page_id', 'tag_id');
    }

    public function media() {
        return $this->hasManyThrough(Media::class, MediaPage::class, 'page_id', 'file_id', 'page_id', 'file_id');
    }
}
