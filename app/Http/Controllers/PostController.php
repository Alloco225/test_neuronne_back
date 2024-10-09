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
        $user = $request->user();
        try {
            $request->validate([
                'q' => 'nullable|string',
                'limit' => 'nullable|string',
                'skip' => 'nullable|integer',
                'select' => 'nullable|string',
                'sortBy' => 'nullable|string',
                'order' => 'nullable|string|in:asc,desc',
            ]);

            $select = explode(',', $request->select);
            $validSelects = Schema::getColumnListing('posts');
            foreach ($select as $value) {
                if (!in_array($value, $validSelects)) {
                    throw new \Exception("select value incorrect '$value'");
                }
            }

            $search = $request->q;
            $limit = $request->limit;
            $skip = $request->skip;
            $sortBy = $request->sortBy;
            $order = $request->order ?? 'desc';

            $posts = Post::when($search, function($query, $search){
                return $query
                    ->where('title', 'like', "%$search%")
                    ->orWhere('slug', 'like', "%$search%")
                    ->orWhere('content', 'like', "%$search%");
            })
                ->when($sortBy, function ($query, $sortBy) use ($order) {
                    return $query
                        ->when($order, function ($query, $order) use ($sortBy) {
                            $query->orderBy($sortBy, $order);
                        });
                }, function ($query) use ($order) {
                    return $query->orderBy('last_update', $order);
                })
                ->when($select, function ($query, $select) {
                    return $query->select($select);
                })
                ->when($skip, function ($query, $skip) {
                    return $query->skip($skip);
                })
                ->when($limit, function ($query, $limit) {
                    return $query->limit($limit);
                })
                ->where('user_id', $user->id)

                // ->with('user')
                ->get();
            return response()->json($posts);
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
        $user = $request->user();
        try {

            $validatedData = $request->validate([
                'title' => "required|string",
                'content' => "required",
                // 'image_file' => "required|file|mimes:png,jpg",
            ]);
            $imagePath = null;
            if ($request->hasFile('image_file')) {
                $image = $request->file('image_file');
                $path = $image->storeAs('public/posts', $validatedData['slug'] . '_' . time() . '.' . $image->getClientOriginalExtension());
                $imagePath = str_replace('public', '', $path);
            }
            $post = Post::create([
                'user_id' => $user->id,
                'title' => $validatedData['title'],
                'content' => $validatedData['content'],
                'image_path' => $imagePath,
            ]);

            return response()->json($post);
        } catch (\Throwable $th) {
            if (isset($imagePath)) {
                \Storage::delete('public/' . $imagePath);
            }
            // if(env('APP_DEBUG')){
            //     throw $th;
            // }
            return response()->json($th->getMessage(), 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $slugOrId)
    {
        $user = $request->user();
        // $post = Post::where('slug', $slugOrId)->orWhere('id', $slugOrId)->first();
        $post = $user->posts->where('slug', $slugOrId)->orWhere('id', $slugOrId)->first();
        if (!$post) {
            return response()->json(null, 404);
        }
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
    public function update(Request $request, $postId)
    {
        try {
            $user = $request->user();

            // $post = Post::findOrFail($postId);
            $post = $user->posts->findOrFail($postId);

            $validatedData = $request->validate([
                'title' => "required|string",
                'content' => "required",
                // 'image_file' => "required|file|mimes:png,jpg",
            ]);
            $imagePath = null;
            if ($request->hasFile('image_file')) {
                $image = $request->file('image_file');
                $path = $image->storeAs('public/posts', $validatedData['slug'] . '_' . time() . '.' . $image->getClientOriginalExtension());
                $imagePath = str_replace('public', '', $path);
                $oldImage = $post->image_path;
                if (isset($oldImage)) {
                    \Storage::delete('public/' . $oldImage);
                }
                $post->image_path = $imagePath;
                $post->save();
            }
            $post->title = $validatedData['title'];
            $post->content = $validatedData['content'];
            $post->save();

            return response()->json($post);
        } catch (\Throwable $th) {
            if (isset($imagePath)) {
                \Storage::delete('public/' . $imagePath);
            }
            // if(env('APP_DEBUG')){
            //     throw $th;
            // }
            return response()->json($th->getMessage(), 400);
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $postId)
    {
        //
        $user = $request->user();
        // $post = Post::find($postId);
        $post = $user->posts->find($postId);
        if (!$post) {
            return response()->json(false, 404);
        }
        $post->delete();
        return response()->json(true);
    }
}
