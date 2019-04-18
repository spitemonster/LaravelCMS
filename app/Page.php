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
        return $this->hasMany(Page::class, 'parent_id', 'page_id');
    }

    public function values()
    {
        return $this->hasMany(FieldValue::class, 'page_id', 'page_id');
    }
}
