<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Post extends Model
{
    /** @use HasFactory<\Database\Factories\PostFactory> */
    use HasFactory;

    protected $guarded = [];
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'last_update';

    protected static function booted(): void
    {
        // * Mettre à jour le slug automatiquement à partir du titre
        // static::created(function (Post $post) {
        static::created(function (Post $post) {
            $post->slug = Str::slug($post->title);
            $post->save();
        });
        static::updated(function (Post $post) {
            $newSlug = Str::slug($post->title);

            if($post->slug != $newSlug){
                $post->slug = $newSlug;
                $post->save();
            }
        });
    }

    // * image_url attribut calculé
    public function getImageUrlAttribute(){
        return $this->image_path;
    }


    // ** RELATIONSHIPS

    public function user(){
        return $this->belongsTo(User::class);
    }
}
