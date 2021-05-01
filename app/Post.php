<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Post extends Model
{
    protected $fillable = ['result','files','error','category_id','alias', 'station_id'];

    public function station()
    {
        return $this->belongsTo('App\Station');
    }
    public function category()
    {
        return $this->belongsTo('App\Category');
    }

    public function folder()
    {
        return $this->belongsTo('App\Folder', 'alias', 'name');
    }


}
