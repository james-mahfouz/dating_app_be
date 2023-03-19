<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register']]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        $credentials = $request->only('email', 'password');

        $token = Auth::attempt($credentials);
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = Auth::user();
        return response()->json([
                'status' => 'success',
                'user' => $user,
                'authorisation' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ]);

    }

    public function register(Request $request){
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'gender' => 'required|string|in:Male,Female',
            'country' => 'required|string|max:100',
            'age' => 'required|integer|between:18,64',
        ]);
        $gender = $request->gender == 'Male' ? 1 : 2;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'country' => $request->country,
            'genders_id' => $gender,
            'age' => $request->age,
        ]);

        $token = Auth::login($user);
        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }

    public function change_infos(Request $request){
        $request->validate([
            'name' => 'required|string|max:255',
            'country' => 'required|string|max:100',
            'description' => 'required|string|max:255',
        ]);
        $user = auth()->user();
        $user->name = $request->input('name');
        $user->country = $request->input('country');
        $user->description = $request->input('description');
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Name updated successfully',
            'user' => $user,
        ]);
    }

    public function getOppositeGenderUsers(Request $request)
    {
        $gender = $request->input('gender');

        $oppositeGender = ($gender == 'male') ? 'female' : 'male';

        $users = User::where('gender', $oppositeGender)->get();

        return response()->json($users);
    }

}
