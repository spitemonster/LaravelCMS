<?php

namespace App;

use App\Field;
use App\Page;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    public function fields()
    {
        return $this->hasMany(Field::class, 'template_id', 'template_id');
    }

    public function pages()
    {
        return $this->hasMany(Page::class);
    }
}
