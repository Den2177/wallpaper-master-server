<?php

namespace App\Http\Controllers;

use App\Http\Resources\ImageResource;
use App\Models\Image;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ImageController extends Controller
{
    public function index(Request $request)
    {
        $offset = $request->input('offset');
        if (!$offset) $offset = 0;

        return ImageResource::collection(Auth::user()->images()
            ->offset($offset)->limit(Image::$imagesPerOneLoad)
            ->orderByDesc('created_at')->get());
    }

    public function getRecommendedImages(Request $request)
    {
        $offset = $request->input('offset');

        if (!$offset) $offset = 0;

        $images = Image::query()->offset($offset)->limit(Image::$imagesPerOneLoad)->get();

        return ImageResource::collection($images);
    }

    public function getUserImages(Request $request, User $user)
    {
        $offset = $request->input('offset');
        if (!$offset) $offset = 0;

        return ImageResource::collection($user->images()->offset($offset)->limit(Image::$imagesPerOneLoad)->get());
    }

    public function getTopImages(Request $request)
    {
        $offset = $request->input('offset');
        if (!$offset) $offset = 0;

        $name = $request->input('name');

        return ImageResource::collection(Image::getTopImages($name, $offset));
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $validator = validator($data, [
            'image' => 'required|image|mimes:png,jpg,jpeg|dimensions:min_width=200,min_height=200',
            'name' => 'nullable|string',
            'tags' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return Controller::sendBadRequest($validator->errors());
        }

        $image = $request->file('image');
        $name = Str::random() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('images/'), $name);
        $data['url'] = url('/images/' . $name);
        unset($data['image']);

        if (isset($data['tags'])) {
            $tags = $data['tags'];
            unset($data['tags']);
        }

        if (empty($data['name'])) {
            $data['name'] = 'Untitled';
        }

        $data['user_id'] = Auth::user()->id;
        $imageObj = Image::create($data);

        if (!empty($tags)) {
            $tagsIds = collect($tags)->map(function($name) {
                $tag = Tag::create([
                   'name' => $name
                ]);

                return $tag->id;
            });

            $imageObj->tags()->attach($tagsIds);
        }

        $statistic = Auth::user()->statistic;
        $statistic->increment('uploads');

        return response()->json(
            [
                'successful' => true,
                'data' => $imageObj
            ], 200
        );
    }

    public function show(Image $image)
    {
        return new ImageResource($image);
    }

    public function download(Image $image)
    {
        $statistic = Auth::user()->statistic;
        $statistic->increment('downloads');

        return response()->download($image->getPath());
    }

    public function toggleLike(Image $image)
    {
        $image->toggleLike();

        return response()->json(
            [
                'isLiked' => $image->isLiked(),
                'likes' => $image->likes,
            ]
        );
    }

    public function indexLiked(Request $request)
    {
        $offset = $request->input('offset');

        if (!$offset) {
            $offset = 0;
        }
        return ImageResource::collection(Image::getLiked($offset));
    }
}
