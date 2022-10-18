<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function getStatistic()
    {
        return null;
    }

    public function index(User $user)
    {
        return new UserResource($user);
    }

    public function updateProfile(Request $request)
    {
        $data = $request->all();

        $validator = validator($data, [
            'name' => 'nullable|string',
            'lastname' => 'nullable|string',
            'avatar' => 'nullable|image|mimes:png,jpeg,jpg',
            'email' => 'nullable|email',
        ]);

        if ($validator->fails()) {
            return Controller::sendBadRequest($validator->errors());
        }

        unset($data['_method']);
        $data = collect($data)->filter(function($field) {
            return $field;
        })->toArray();

        if (isset($data['avatar'])) {
            $avatar = $request->file('avatar');
            $name = Str::random() . '.' . $avatar->getClientOriginalExtension();
            $avatar->move(public_path('images'), $name);
            $data['avatar'] = url('images/'.$name);
        }

        $user = Auth::user();
        $user->update($data);

        return response()->json([
            'success' => true,
            'data' => new UserResource($user),
        ]);
    }
}
