<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    public function parent()
    {
        return $this->belongsTo(Page::class);
    }

    public function children()
    {
        return $this->hasMany(Page::class, 'parent_id');
    }
}
