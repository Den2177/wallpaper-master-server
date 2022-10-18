<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\Statistic;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->all();

        $validator = validator($data, [
            'name' => 'required|string',
            'lastname' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string',
            'password_repeat' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return Controller::sendBadRequest($validator->errors());
        }

        $data['token'] = Str::random() . uniqid();
        $data['password'] = Hash::make($data['password']);
        unset($data['password_repeat']);

        $user = User::create($data);

        $statistic = Statistic::create([
            'user_id' => $user->id,
        ]);

        return response()->json(
            [
                'success' => true,
                'data' => [
                    'token' => $user->token,
                ],
            ], 200
        );
    }

    public function login(Request $request)
    {
        $data = $request->all();

        $validator = validator($data, [
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return Controller::sendBadRequest($validator->errors());
        }

        if (!Auth::attempt($validator->validated())) {
            return response()->json(
                [
                    'success' => false,
                    'errors' => [
                        'email' => ['email or password incorrect'],
                    ]
                ], 422
            );
        }

        $user = User::firstWhere('email', $data['email']);

        $user->update([
            'token' => Str::random() . uniqid(),
        ]);

        return response()->json(
            [
                'success' => true,
                'data' => [
                    'token' => $user->token,
                ],
            ]
        );
    }

    public function auth()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
            ]);
        }

        return response()->json(
            [
                'success' => true,
                'data' => new UserResource($user),
            ]
        );
    }
}
