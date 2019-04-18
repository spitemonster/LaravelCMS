<?php

namespace App\Traits;
use App\Tag;

trait Taggable
{
    public function Tags()
    {
        return $this->belongsTo(Tag::class, 'page_id', 'page_id');
    }
}