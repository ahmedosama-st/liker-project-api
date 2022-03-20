<?php

namespace App\Http\Controllers;

use App\Events\PostWasLiked;
use App\Models\Post;
use Illuminate\Http\Request;
use function broadcast;

class PostLikeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum']);
    }
    
    public function store(Request $request, Post $post)
    {
       $like = $post->likes()->make();
       $like->user()->associate($request->user());

       $like->save();

       broadcast(new PostWasLiked($post))->toOthers();

       return response()->json([
           'likes' => $post->likes->count()
       ]);
    }
}
