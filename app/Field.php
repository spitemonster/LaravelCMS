<?php

namespace App;

use App\field_value;
use Illuminate\Database\Eloquent\Model;

class Field extends Model
{
    protected $touches = ['template'];

    public function template()
    {
        return $this->belongsTo(Template::class, 'template_id', 'template_id');
    }

    public function getRequiredAttribute($value)
    {
        return (bool) $value;
    }
}
