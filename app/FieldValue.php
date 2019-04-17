<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FieldValue extends Model
{
    protected $touches = ['field', 'page'];

    public function page()
    {
        return $this->belongsTo(Page::class, 'page_id', 'page_id');
    }

    public function field()
    {
        return $this->belongsTo(Field::class, 'field_id', 'field_id');
    }
}
