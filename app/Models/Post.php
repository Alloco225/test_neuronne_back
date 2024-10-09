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
            $post->updateSlug();
        });
        static::updated(function (Post $post) {
            $post->updateSlug();
        });
    }

    public function updateSlug()
    {
        $slug = Str::slug($this->title);
        if(!$this->slug){
            if (self::where('slug', $slug)->first()) {
                throw new \Exception(message: __("validation.unique", ['attribute' => 'title']));
            }
        }
        if ($this->slug == $slug) {
            return;
        }
        $this->slug = $slug;
        $this->save();
    }

    // * image_url attribut calculé
    public function getImageUrlAttribute()
    {
        return $this->image_path;
    }

    // ** RELATIONSHIPS

    public function author()
    {
        return $this->user;
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
