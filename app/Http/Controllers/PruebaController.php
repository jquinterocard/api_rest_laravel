<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Post;
use App\Category;

class PruebaController extends Controller
{
    
    public function textOrm(){
    	// $posts = Post::all();
    	// foreach ($posts as $post) {
    	// 	echo '<h1>'.$post->title.'</h1>';
    	// 	echo '<span style="color:gray">'.$post->user->name.' - '.$post->category->name.'</span>';
    	// 	echo '<p>'.$post->content.'</p>';
    	// 	echo '<hr>';
    	// }


    	$categories = Category::all();
    	foreach ($categories as $category) {
    		echo '<h1>'.$category->name.'</h1>';
    		// posts de esta categoria
    		foreach ($category->posts as $post) {
    				echo '<h3>'.$post->title.'</h3>';
    				echo '<span style="color:gray">'.$post->user->name.' - '.$post->category->name.'</span>';
    				echo '<p>'.$post->content.'</p>';
    		}
    		echo '<hr>';
    	}
    	die();
    }


}
