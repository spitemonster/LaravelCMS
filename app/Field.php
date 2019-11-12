<?php

namespace App;

use App\FieldValue;
use Illuminate\Database\Eloquent\Model;

class Field extends Model
{
    protected $touches = ['template'];

    public function template()
    {
        return $this->belongsTo(Template::class, 'template_id', 'template_id');
    }

    public function values() {
        return $this->hasMany(FieldValue::class, 'field_id', 'field_id');
    }

    public function getRequiredAttribute($value)
    {
        return (bool) $value;
    }
}
