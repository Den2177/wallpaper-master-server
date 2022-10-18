<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $path = $this->getPath();
        $imageSize = getimagesize($path);

        return [
            "id" => $this->id,
            'name' => $this->name,
            'url' => $this->url,
            'width' => $imageSize[0],
            'height' => $imageSize[1],
            'mime' => $imageSize['mime'],
            'filesize' => filesize($path),
            'likes' => $this->likes,
            'isLiked' => (Auth::check()) ? $this->isLiked() : false,
            'author' => new UserResource($this->user),
            'tags' => $this->tags,
        ];
    }
}
