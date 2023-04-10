<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Follow;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function createFollow(User $user)
    {
        // you cannot follow your self
        if (auth()->user()->id == $user->id) {
            return back()->with('error', 'you cannot follow your-self!');
        }

        // you cannot follow someone you have followed
        $existCheck = Follow::where([
                ['user_id', '=', auth()->user()->id], 
                ['followeduser', '=', $user->id]
            ])->count();

        if ($existCheck) {
            return back()->with('error', 'you already follow this user!');
        }

        $newFollow = new Follow;
        $newFollow->user_id = auth()->user()->id;
        $newFollow->followeduser = $user->id;
        $newFollow->save();

        return back()->with('success', 'you successfully follow this user!');
    }

    public function removeFollow(User $user)
    {
        Follow::where([
            ['user_id', '=', auth()->user()->id], 
            ['followeduser', '=', $user->id]
        ])->delete();

        return back()->with('success', 'you successfully unfollow this user!');
    }
}
