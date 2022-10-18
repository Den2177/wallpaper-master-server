<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;


class Image extends Model
{
    use HasFactory;

    protected $guarded = false;
    protected $table = 'images';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'image_tags', 'image_id', 'tag_id');
    }

    public function getPath()
    {
        return public_path(preg_replace("/http:\/\/.+?\//", "", $this->url));
    }

    public function getName()
    {
        return preg_replace("/http:\/\/.+?\/images\//", "", $this->url);
    }

    public function toggleLike()
    {
        $userId = Auth::user()->id;
        $likeIsSetted = $this->isLiked();

        if ($likeIsSetted) {
            $this->likedUsers()->detach([$userId]);
            $this->decrement('likes');
            Auth::user()->statistic->decrement('likes');
        } else {
            $this->likedUsers()->attach([$userId]);
            $this->increment('likes');
            Auth::user()->statistic->increment('likes');
        }

        $this->load('likedUsers');

        return true;
    }

    public function isLiked()
    {
        return $this->likedUsers->contains(Auth::user()->id);
    }

    public function likedUsers()
    {
        return $this->belongsToMany(User::class, 'image_likes', 'image_id', 'user_id');
    }

    public static function getTopImages($name)
    {
        $imagesByName = Image::where('name', 'like', "%$name%")->get();
        $imagesByTags = collect([]);
        $imagesByTagsIds = [];

        $tags =  Tag::query()->where('name', 'like', "%$name%")->get();

        foreach ($tags as $tag) {
            $tagImages = $tag->images;

            foreach ($tagImages as $tagImage) {
                if (in_array($tagImage->id, $imagesByTagsIds)) continue;

                $imagesByTags->push($tagImage);
                $imagesByTagsIds[] = $tagImage->id;
            }
        }

        return $imagesByName->merge($imagesByTags);
    }

    public static function getLiked()
    {
        return Auth::user()->likedImages()->orderByDesc('created_at')->get();
    }
}
