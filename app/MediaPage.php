<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MediaPage extends Model
{
    public function page() {
        return $this->belongsTo(Page::class, 'page_id', 'page_id');
    }

    public function media() {
        return $this->belongsTo(Media::class, 'file_id', 'file_id');
    }
}
