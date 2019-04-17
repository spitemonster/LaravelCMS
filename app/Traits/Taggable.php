<?php

namespace App\Traits;
use App\Tag;

trait Taggable
{
    public function Tags()
    {
        return $this->hasMany(Tag::class, 'tag_id');
    }
}