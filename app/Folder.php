<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    public function posts()
    {
        return $this->hasMany('App\Post', 'name', 'alias');
    }
}
