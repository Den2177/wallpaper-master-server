<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $guarded = false;

    public function images()
    {
        return $this->belongsToMany(Image::class, 'image_tags', 'tag_id', 'image_id');
    }
}
