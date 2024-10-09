<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $search = $request->q;
        $limit = $request->limit;
        $skip = $request->skip;
        $select = $request->select;
        $sortBy = $request->sortBy;
        $order = $request->order ?? 'desc';

        // $posts =  Post::when($search, function($query, $search){
        $posts = $user->posts->when($search, function($query, $search){
            return $query
            ->where('title', 'like', "%$search%")
            ->orWhere('slug', 'like', "%$search%")
            ->orWhere('content', 'like', "%$search%");
        })
        ->when($sortBy, function($query, $sortBy) use ($order){
            return $query
            ->when($order, function($query, $order) use ($sortBy){
                $query->sortBy($sortBy, $order);
            });
        }, function($query){
            return $query->latest();
        })
        ->when($select, function($query, $select){
            return $query->select($select);
        })
        ->when($skip, function($query, $skip){
            return $query->skip($skip);
        })
        ->when($limit, function($query, $limit){
            return $query->limit($limit);
        });
        return response()->json($posts);
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
