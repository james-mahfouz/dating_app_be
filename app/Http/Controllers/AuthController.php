<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

use App\Models\User;
use App\Models\Picture;
use App\Models\Block;
use App\Models\Favorite;
use App\Models\Users_chat;

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
        $profile_picture = $user->pictures->first;
        $profile_picture_path = $profile_picture ? $profile_picture->path : null;

        return response()->json([
                'status' => 'success',
                'user' => $user,
                'profile_picture_path' => $profile_picture_path,
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

    public function get_users(Request $request)
    {
        $user = auth()->user();
        $gender = $user->genders_id;

        $oppositeGender = ($gender == 1) ? 2 : 1;

        $users = User::with(['pictures' => function($query) {
                $query->select(['user_id', 'path']);
            }])
            ->where('genders_id', $oppositeGender)
            ->whereDoesntHave('blocked', function ($query) {
                $query->where('blocking', auth()->user()->id);
            })
            ->get();

        return response()->json($users);
    }

    public function upload_picture(Request $request)
    {
        $user = $request->user();
        $previous_picture = $user->pictures()->first();
        
        if ($previous_picture!=null) {
            Storage::disk('public')->delete($previous_picture->path);
            $previous_picture->delete();
        }

        $file = $request->file('profile_picture');
        $path = $file->store('profile-pictures', 'public');

        $picture = new Picture();
        $picture->path = $path;
        $picture->user_id = $user->id;
        $picture->save();

        return response()->json([
            'message' => 'Profile picture uploaded successfully.',
            'path' => $path
        ]);
    }

    public function block(User $user)
    {
        $currentUser = auth()->user();

        if ($currentUser->blocking()->where('blocked', $user->id)->exists()) {
            return response()->json(['message' => 'User is already blocked.'], 409);
        }

        $block = new Block();
        $block->blocking = $currentUser->id;
        $block->blocked = $user->id;
        $block->save();

        return response()->json(['message' => 'User has been blocked.'], 200);
    }

    public function favorite(User $user)
    {
        $currentUser = auth()->user();

        if ($currentUser->favorating()->where('favorated', $user->id)->exists()) {
            return response()->json(['message' => 'User is already blocked.'], 409);
        }

        $favorite = new Favorite();
        $favorite->favorating = $currentUser->id;
        $favorite->favorated = $user->id;
        $favorite->save();

        return response()->json(['message' => 'User has been added to favorites.'], 200);
    }

    public function chat(User $user)
    {
        $currentUser = auth()->user();

        if ($currentUser->chatting()->where('users_id2', $user->id)->exists()) {
            return response()->json(['message' => 'Already chatting'], 409);
        }
        if ($currentUser->chatted()->where('users_id1', $user->id)->exists()) {
            return response()->json(['message' => 'Already chatting'], 409);
        }

        $users_chat = new Users_chat();
        $users_chat->users_id1 = $currentUser->id;
        $users_chat->users_id2 = $user->id;
        $users_chat->save();

        return response()->json(['message' => 'User can chat with the user.'], 200);
    }

}
