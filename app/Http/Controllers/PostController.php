<?php

namespace App\Http\Controllers;

use App\Events\PostWasCreated;
use App\Http\Requests\StorePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use JetBrains\PhpStorm\Pure;
use function broadcast;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum'])->only(['store']);
    }

    /**
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $posts = Post::with('user', 'likes')
            ->take(5)
            ->skip($request->get('skip', 0) + (($request->get('page') - 1) * 5))
            ->latest()
            ->get();

        return (PostResource::collection($posts))->additional([
            'likes' => $posts->mapWithKeys(function ($post) {
                return [$post->id => $post->likes->count()];
            }),
        ]);
    }

    /**
     * @param \App\Models\Post $post
     *
     * @return \App\Http\Resources\PostResource
     */
    #[Pure] public function show(Post $post): PostResource
    {
        return (new PostResource($post))->additional([
            'likes' => [
                $post->id => $post->likes->count(),
            ],
        ]);
    }

    /**
     * @param \App\Http\Requests\StorePostRequest $request
     *
     * @return \App\Http\Resources\PostResource
     */
    public function store(StorePostRequest $request): PostResource
    {
        $post = $request->user()->posts()->create($request->only('body'));

        broadcast(new PostWasCreated($post))->toOthers();

        return (new PostResource($post))->additional([
                'likes' => [
                    $post->id => $post->likes->count(),
                ],
            ]);
    }
}
