<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Post;
use App\Models\User;
use App\Models\Follow;
use Illuminate\Http\Request;
use App\Events\OurExampleEvent;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $incomingFields = $request->validate([
            'username' => ['required', 'min:3', 'max:20', Rule::unique('users', 'username')],
            'email' => ['required', 'email', Rule::unique('users', 'email')],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $incomingFields['password'] = bcrypt($incomingFields['password']);

        $user = User::create($incomingFields);

        auth()->login($user);

        return redirect('/')->with('success','account created successfully!');
    }

    public function login(Request $request)
    {
        $incomingFields = $request->validate([
            'loginusername' => 'required',
            'loginpassword' => 'required',
        ]);

        if (auth()->attempt([
            'username' => $incomingFields['loginusername'],
            'password' => $incomingFields['loginpassword'],
            ])) {
            $request->session()->regenerate();

            event(new OurExampleEvent([
                'username' => auth()->user()->username,
                'action' => 'login'
            ]));

            return redirect('/')->with('success','you have successfully log in!');
        }
        else {
            return redirect('/')->with('error','invalid login');
        }
    }

    public function showCorrectHomepage()
    {
        if (auth()->check()) {
            $list_post = DB::table('follows')
            ->join('posts', 'follows.followeduser', '=', 'posts.user_id')
            ->join('users', 'follows.followeduser', '=', 'users.id')
            ->where('follows.user_id', '=', auth()->user()->id)
            ->select('posts.id', 'posts.title', 'posts.body', 'posts.created_at', 'users.avatar', 'users.username')
            ->paginate(4);

            foreach ($list_post as $data) {
                // If "avatar" is null, set it to a default value
                if (is_null($data->avatar)) {
                    $data->avatar = '/fallback-avatar.jpg';
                }else {
                    $data->avatar = '/storage/avatars/'.$data->avatar;
                }

                // format date
                $date = Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at);
                $data->created_at = $date->format('d/m/Y');
            }

            return view('homepage-feed',[
                'list_post' => $list_post
            ]);
        }else {
            if (Cache::has('postCount')) {
                $postCount = Cache::get('postCount');
            } else {
                $postCount = Post::count();
                Cache::put('postCount', $postCount, 60);
            }
            
            return view('homepage',['postCount'=>$postCount]);
        }
    }

    public function logout()
    {
        event(new OurExampleEvent([
            'username' => auth()->user()->username,
            'action' => 'logout'
        ]));

        auth()->logout();
        return redirect('/')->with('success','you are now log out!');
    }

    public function profile(User $user)
    {
        $post = $user->post()->latest()->get();
        $post_count = $user->post()->count();

        // following count
        $following_count = DB::table('follows')
            ->where('user_id', '=', $user->id)
            ->count();

        // followers count
        $followers_count = DB::table('follows')
            ->where('followeduser', '=', $user->id)
            ->count();

        // follow check
        $followCheck = 0;
        if (auth()->check()) {
            $followCheck = Follow::where([
                ['user_id', '=', auth()->user()->id], 
                ['followeduser', '=', $user->id]
            ])->count();
        }
        
        return view('profile-posts', [
            'user' => $user,
            'post' => $post,
            'post_count' => $post_count,
            'followCheck' => $followCheck,
            'following_count' => $following_count,
            'followers_count' => $followers_count,
            'title' => $user->username. "'s profile"
        ]);
    }

    public function profileFollowers(User $user)
    {
        $list_followers = DB::table('follows')
            ->leftJoin('users', 'follows.user_id', '=', 'users.id')
            ->where('follows.followeduser', '=', $user->id)
            ->select('users.id', 'users.username', 'users.avatar')
            ->get();

        $post_count = $user->post()->count();

        // following count
        $following_count = DB::table('follows')
            ->where('user_id', '=', $user->id)
            ->count();

        // followers count
        $followers_count = DB::table('follows')
            ->where('followeduser', '=', $user->id)
            ->count();

        // follow check
        $followCheck = 0;
        if (auth()->check()) {
            $followCheck = Follow::where([
                ['user_id', '=', auth()->user()->id], 
                ['followeduser', '=', $user->id]
            ])->count();
        }
        
        return view('profile-followers', [
            'user' => $user,
            'list_followers' => $list_followers,
            'post_count' => $post_count,
            'followCheck' => $followCheck,
            'following_count' => $following_count,
            'followers_count' => $followers_count,
            'title' => $user->username. "'s followers"
        ]);

    }

    public function profileFollowing(User $user)
    {
        $list_following = DB::table('follows')
            ->leftJoin('users', 'follows.followeduser', '=', 'users.id')
            ->where('follows.user_id', '=', $user->id)
            ->select('users.id', 'users.username', 'users.avatar')
            ->get();

        $post_count = $user->post()->count();

        // following count
        $following_count = DB::table('follows')
            ->where('user_id', '=', $user->id)
            ->count();

        // followers count
        $followers_count = DB::table('follows')
            ->where('followeduser', '=', $user->id)
            ->count();

        // follow check
        $followCheck = 0;
        if (auth()->check()) {
            $followCheck = Follow::where([
                ['user_id', '=', auth()->user()->id], 
                ['followeduser', '=', $user->id]
            ])->count();
        }
        
        return view('profile-following', [
            'user' => $user,
            'list_following' => $list_following,
            'post_count' => $post_count,
            'followCheck' => $followCheck,
            'following_count' => $following_count,
            'followers_count' => $followers_count,
            'title' => $user->username. "'s following"
        ]);

    }

    public function showAvatarForm()
    {
        return view('avatar-form',['title' => "Manage Your Avatar"]);
    }

    public function storeAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image'
        ]);

        $user = auth()->user();
        $filename = $user->id . '_' . uniqid() . '.jpg';

        // $request->file('avatar')->store('public/avatars');
        $img = Image::make($request->file('avatar'))->fit(120)->encode('jpg');
        Storage::put('public/avatars/' . $filename, $img);

        $oldAvatar = $user->avatar;

        $user->avatar = $filename;
        $user->save();

        if ($oldAvatar != "/fallback-avatar.jpg") {
            $oldAvatarLocation = str_replace('/storage/', '/public/', $oldAvatar);
            Storage::delete($oldAvatarLocation);
        }
        
        return back()->with('success', 'image changed successfully!');
    }
}
