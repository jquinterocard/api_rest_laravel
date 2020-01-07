<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table = 'posts';

    // en estos campos puedo hacer update de manera masiva
    protected $fillable = [
    	'title','content','category_id','image'
    ];

    // Relacion muchos a uno inversa
    public function user(){
    	return $this->belongsTo('App\User','user_id');
    }

    public function category(){
    	return $this->belongsTo('App\Category','category_id');
    }


}
