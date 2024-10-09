<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Schema;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // $user = $request->user();
        try {
            $request->validate([
                'q' => 'nullable|string',
                'limit' => 'nullable|string',
                'skip' => 'nullable|integer',
                'select' => 'nullable|string',
                'sortBy' => 'nullable|string',
                'order' => 'nullable|string|in:asc,desc',
            ]);

            $select = explode(',',$request->select);
            $validSelects =  Schema::getColumnListing('posts');
            foreach ($select as $value) {
                if(!in_array($value, $validSelects)){
                    throw new \Exception("select value incorrect '$value'");
                }
            }

            $search = $request->q;
            $limit = $request->limit;
            $skip = $request->skip;
            $sortBy = $request->sortBy;
            $order = $request->order ?? 'desc';
            $posts_unfiltered = Post::all();
            // $posts = $user->posts->when($search, function($query, $search){
            $posts =  Post::when($search, function($query, $search){
                return $query
                ->where('title', 'like', "%$search%")
                ->orWhere('slug', 'like', "%$search%")
                ->orWhere('content', 'like', "%$search%");
            })
            ->when($sortBy, function($query, $sortBy) use ($order){
                return $query
                ->when($order, function($query, $order) use ($sortBy){
                    $query->orderBy($sortBy, $order);
                });
            }, function($query) use ($order){
                return $query->orderBy('last_update', $order);
            })
            ->when($select, function($query, $select){
                return $query->select($select);
            })
            ->when($skip, function($query, $skip){
                return $query->skip($skip);
            })
            ->when($limit, function($query, $limit){
                return $query->limit($limit);
            })->get();
            return response()->json([$posts, $posts_unfiltered]);
        } catch (\Throwable $th) {
            // if(env('APP_DEBUG')){
            //     throw $th;
            // }
            return response()->json($th->getMessage(), 400);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => "required|string",
            'content' => "required",
            'image_file' => "required|file|mimes:png,jpg",
        ]);
        //
        $imagePath = null;
        if($request->hasFile('image_file')){

        }
        $post = Post::create([
            'title' => $validatedData['title'],
            'content' => $validatedData['content'],
        ]);

        return response()->json($post);
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        //

        return response()->json($post);

    }
    public function getPostBySlug(Post $post)
    {
        return response()->json($post);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        //
        $post->delete();
        return response()->json(true);
    }
}
