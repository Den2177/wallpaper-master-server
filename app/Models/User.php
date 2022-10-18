<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $guarded = false;
    protected $table = 'users';

    public function images()
    {
        return $this->hasMany(Image::class);
    }

    public function getRecommendedImages()
    {
        /*i save tags to store and find needed images by tag names (tag - object in database ) */
        /*return $this->images()->where('tagName', 'like', '%%')->get();*/

        return Image::all();
    }
    public function statistic()
    {
        return $this->hasOne(Statistic::class, 'user_id');
    }

    public function likedImages()
    {
        return $this->belongsToMany(Image::class, 'image_likes', 'user_id', 'image_id');
    }

    public static function findByBearer($bearer)
    {
        return User::query()->firstWhere('token', $bearer);
    }
}
