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


    public function toArray()
    {
        $array = parent::toArray();
        $array['image_url'] = $this->image_url;
        return $array;
    }

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
        static::deleted(function (Post $post) {
            // delete image
            if ($post->imagePath) {
                \Storage::delete('public/' . $post->imagePath);
            }
        });
    }

    public function updateSlug()
    {
        $slug = Str::slug($this->title);
        if (!$this->slug) {
            if (self::where('slug', $slug)->first()) {
                $this->delete();
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
        $img = $this->image_path;
        if (!$img) {
            return null;
        }
        if (str_starts_with($img, 'http')) {
            return $img;
        }
        $link = '/storage' . ($img[0] == '/' ? $img : '/' . $img);
        return asset($link);

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
